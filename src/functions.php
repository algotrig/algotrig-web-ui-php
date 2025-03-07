<?php

declare(strict_types=1);

/**
 * Convert an object to an HTML table row
 *
 * @param object $object The object to convert
 * @param bool $header Whether this is a header row
 * @return string HTML table row
 */
function objectToTableRow(object $object, bool $header = false, bool $hideQuoteSymbol = true, bool $hideInstrumentToken = true): string {
    $html = "<tr>";

    foreach ($object as $key => $value) {
        if (($hideQuoteSymbol && $key === "quote_symbol") || ($hideInstrumentToken && $key === "instrument_token")) {
            continue;
        }
        if (($key === "current_value" || $key === "proposed_value") && !$header) {
            $html .= getTd($key, $value, true);
        } else {
            $keyDisplay = strtoupper($header ? str_replace('_', '<br/>', $key) : $key);
            $html .= !$header
                ? getTd($key, $value)
                : "<th>{$keyDisplay}</th>";
        }
    }

    $html .= "</tr>";
    return $html;
}

/**
 * Get a table data element
 *
 * @param string $key The key
 * @param mixed $value The value
 * @param bool $withAnchor Whether to include an anchor element
 */
function getTd($key, $value, bool $withAnchor = false) {
    if ($withAnchor) {
        return "<td class=\"{$key}\">" . getAnchor($value) . "</td>";
    }
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
function getAnchor($targetValue, int $executeOrders = 0, bool $withRefresh = false, int $refreshInterval = 0) {
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
