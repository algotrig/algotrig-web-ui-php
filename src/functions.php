<?php

declare(strict_types=1);

use AlgoTrig\PhpCore\ZerodhaKite;

function submitTrade(ZerodhaKite $kite): array {
    // Validate and sanitize inputs
    $quantity = $_POST['quantity'] ?? '';
    $tradingSymbol = trim($_POST['tradingSymbol'] ?? '');
    $action = $_POST['action'] ?? '';

    // Validate quantity as integer > 0
    if (filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
        throw new RuntimeException("Quantity must be an integer greater than 0.");
    }

    // Convert quantity to integer after validation
    $quantity = (int) $quantity;

    // Validate trading symbol
    if (empty($tradingSymbol)) {
        throw new RuntimeException("Trading symbol is required.");
    }

    $obj = new stdClass();
    $obj->trading_symbol = $tradingSymbol;

    // Validate action and handle accordingly
    if ($action === 'buy') {
        // Perform buy logic
        $obj->buy_qty = $quantity;
        $obj->sell_qty = 0;
    } elseif ($action === 'sell') {
        // Perform sell logic
        $obj->buy_qty = 0;
        $obj->sell_qty = $quantity;
    } else {
        throw new RuntimeException("Invalid action. Must be 'buy' or 'sell'.");
    }

    $order = $kite->getOrder($obj, strtoupper($action));
    $executedOrderData = $kite->executeOrder($order);
    return array(
        'order' => $order,
        'executedOrderData' => $executedOrderData
    );
}

/**
 * Get tbody html for tradingData
 *
 * @param ZerodhaKite $kite The ZerodhaKite object that has tradingData
 * @return string HTML tbody
 */
function getTbody(ZerodhaKite $kite): string {
    $targetValue = $kite->getTargetValue();
    $tbody = "<tbody>";
    foreach ($kite->getTradingData() as $symbol => $row) {
        if (floatval($row->difference) >= 0.0 || floatval($row->current_value) == $targetValue) {
            $tbody .= objectToTableRow($row);
        }
    }
    $tbody .= "</tbody>";
    return $tbody;
}

/**
 * Convert an object to an HTML table row
 *
 * @param object $object The object to convert
 * @param bool $hideQuoteSymbol Whether to hide quote_symbol column, default = true
 * @param bool $hideInstrumentToken Whether to hide instrument_token column, default = true
 * @return string HTML table row
 */
function objectToTableRow(object $object, bool $hideQuoteSymbol = true, bool $hideInstrumentToken = true): string {
    $html = "<tr>";
    foreach ($object as $key => $value) {
        if (($hideQuoteSymbol && $key === "quote_symbol") || ($hideInstrumentToken && $key === "instrument_token")) {
            continue;
        }
        if ($key === "current_value" || $key === "proposed_value") {
            $value = getAnchor($value);
        }
        $html .= getTd($key, $value);
    }
    $html .= getTd("trade-form-action-td", getBuySellForm($object->trading_symbol));
    $html .= "</tr>";
    return $html;
}

function getBuySellForm(string $symbol): string {
    $formHtml  = '<form action="/?action=submit-trade" method="POST" class="trade-form" >';
    //$formHtml .= '<label for="quantity">Qty: </label>';
    $formHtml .= '<input type="number" name="quantity" class="quantity" min="1" step="1" required />';
    $formHtml .= '<input type="hidden" name="tradingSymbol" value="' . htmlspecialchars($symbol) . '" />';
    $formHtml .= '<button type="submit" name="action" value="buy" class="buy-btn">Buy</button>';
    $formHtml .= '<button type="submit" name="action" value="sell" class="sell-btn">Sell</button>';
    $formHtml .= '</form>';
    return $formHtml;
}


/**
 * Convert an object to an HTML table row
 *
 * @param object $object The object to convert
 * @param bool $hideQuoteSymbol Whether to hide quote_symbol column, default = true
 * @param bool $hideInstrumentToken Whether to hide instrument_token column, default = true
 * @return string HTML table header
 */
function objectToTableHeader(object $object, bool $hideQuoteSymbol = true, bool $hideInstrumentToken = true): string {
    $html = "<thead><tr>";
    foreach ($object as $key => $value) {
        if (($hideQuoteSymbol && $key === "quote_symbol") || ($hideInstrumentToken && $key === "instrument_token")) {
            continue;
        }
        $keyDisplay = strtoupper(str_replace('_', '<br/>', $key));
        $html .= "<th>{$keyDisplay}</th>";
    }
    $html .= "<th>ACTION</th>";
    $html .= "</tr></thead>";
    return $html;
}

/**
 * Get a table data element
 *
 * @param string $key The key
 * @param mixed $value The value
 * @return string The HTML Table TD tag string
 */
function getTd($key, $value): string {
    return "<td class=\"{$key}\">{$value}</td>";
}

/**
 * Get an anchor element with the target value
 *
 * @param float $targetValue The target value
 * @param int $executeOrders The number of execute orders
 * @param bool $withRefresh Whether to include a refresh interval
 * @param int $refreshInterval The refresh interval
 * @return string HTML anchor element
 */
function getAnchor($targetValue, int $executeOrders = 0, bool $withRefresh = false, int $refreshInterval = 0): string {
    $refresh = $withRefresh ? "&r={$refreshInterval}" : "";
    return "<a href=\"?execute_orders={$executeOrders}&target_value={$targetValue}{$refresh}\">{$targetValue}</a>";
}

/**
 * Format a number with proper decimal places
 *
 * @param float $number The number to format
 * @param int $decimals Number of decimal places
 * @return string Formatted number
 */
function formatNumber(float $number, int $decimals = 2): string {
    return number_format($number, $decimals, '.', '');
}

/**
 * Validate and sanitize refresh interval
 *
 * @param int $interval The refresh interval in seconds
 * @return int Validated interval
 */
function validateRefreshInterval(int $interval, array $config): int {
    $minInterval = $config['refresh']['min_interval'];
    $maxInterval = $config['refresh']['max_interval'];

    return max($minInterval, min($maxInterval, $interval));
}
