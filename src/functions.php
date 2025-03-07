<?php

declare(strict_types=1);

/**
 * Trading related functions for the AlgoTrig application
 */

/**
 * Get trading symbols from holdings
 *
 * @param array $holdings Array of holding objects
 * @return array{ trading_symbols: array, quote_symbols: array, holding_keys: array }
 */
function getTradingSymbols(array $holdings): array
{
    $holdingKeys = [];
    $tradingSymbols = [];
    $quoteSymbols = [];

    foreach ($holdings as $index => $holding) {
        $tradingSymbol = $holding->tradingsymbol;
        $quoteSymbol = "NSE:" . $tradingSymbol;
        
        $tradingSymbols[] = $tradingSymbol;
        $quoteSymbols[] = $quoteSymbol;
        $holdingKeys[$tradingSymbol] = $index;
    }

    return [
        'trading_symbols' => $tradingSymbols,
        'quote_symbols' => $quoteSymbols,
        'holding_keys' => $holdingKeys
    ];
}

/**
 * Convert an object to an HTML table row
 *
 * @param object $object The object to convert
 * @param bool $header Whether this is a header row
 * @return string HTML table row
 */
function objectToTableRow(object $object, bool $header = false): string
{
    $html = "<tr>";
    
    foreach ($object as $key => $value) {
        if (($key === "current_value" || $key === "proposed_value") && !$header) {
            $html .= "<td class=\"{$key}\"><a href=\"?execute_orders=0&target_value={$value}\">{$value}</a></td>";
        } else {
            $keyDisplay = strtoupper($header ? str_replace('_', '<br/>', $key) : $key);
            $html .= !$header 
                ? "<td class=\"{$key}\">{$value}</td>" 
                : "<td>{$keyDisplay}</td>";
        }
    }

    $html .= "</tr>";
    return $html;
}

/**
 * Generate order data for a trading symbol
 *
 * @param object $obj The trading object
 * @param object $kite The KiteConnect instance
 * @return array Order data
 */
function getOrder(object $obj, object $kite): array
{
    $isSpecialSymbol = in_array($obj->trading_symbol, ["FMCGIETF", "HDFCSENSEX"]);
    
    if ($isSpecialSymbol) {
        $quoteSymbols = [$obj->quote_symbol];
        $quotes = $kite->getQuote($quoteSymbols);
        $price = $quotes[$obj->quote_symbol]->depth->sell[4]->price;
        
        return [
            "tradingsymbol" => $obj->trading_symbol,
            "exchange" => "NSE",
            "quantity" => $obj->buy_qty,
            "transaction_type" => "BUY",
            "order_type" => "LIMIT",
            "price" => $price,
            "product" => "CNC"
        ];
    }

    return [
        "tradingsymbol" => $obj->trading_symbol,
        "exchange" => "NSE",
        "quantity" => $obj->buy_qty,
        "transaction_type" => "BUY",
        "order_type" => "MARKET",
        "product" => "CNC"
    ];
}

/**
 * Format a number with proper decimal places
 *
 * @param float $number The number to format
 * @param int $decimals Number of decimal places
 * @return string Formatted number
 */
function formatNumber(float $number, int $decimals = 2): string
{
    return number_format($number, $decimals, '.', '');
}

/**
 * Validate and sanitize refresh interval
 *
 * @param int $interval The refresh interval in seconds
 * @return int Validated interval
 */
function validateRefreshInterval(int $interval, array $config): int
{
    $minInterval = $config['refresh']['min_interval'];
    $maxInterval = $config['refresh']['max_interval'];
    
    return max($minInterval, min($maxInterval, $interval));
} 