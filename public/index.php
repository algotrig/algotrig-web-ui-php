<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/config.php';

use KiteConnect\KiteConnect;

// Load configuration
try {
    $config = loadConfig(__DIR__ . '/../algotrig.ini');
} catch (RuntimeException $e) {
    die("Configuration Error: " . $e->getMessage());
}

// Set error reporting based on environment
error_reporting($config['app']['debug'] ? E_ALL : 0);
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');

// Set timezone
date_default_timezone_set($config['app']['timezone']);

session_start();

// Check authentication
if (!isset($_SESSION['access_token'])) {
    header('Location: /logout.php');
    exit;
}

// Validate and sanitize input
$refreshInterval = validateRefreshInterval(
    isset($_GET['r']) ? (int)$_GET['r'] : $config['refresh']['default_interval'], $config
);
$targetValue = isset($_GET['target_value']) ? (float)$_GET['target_value'] : 0.0;
$executeOrders = isset($_GET['execute_orders']) ? (int)$_GET['execute_orders'] : 0;

// Set refresh header
header("Refresh: {$refreshInterval}");

// Initialize KiteConnect
try {
    $kite = new KiteConnect(
        $config['zerodha']['api_key'],
        $_SESSION['access_token']
    );
} catch (Exception $e) {
    error_log("KiteConnect initialization failed: " . $e->getMessage());
    header('Location: /logout.php');
    exit;
}

// Get positions and holdings
try {
    $positions = $kite->getPositions();
    $holdings = $kite->getHoldings();
} catch (Exception $e) {
    error_log("Failed to fetch positions/holdings: " . $e->getMessage());
    $positions = new stdClass();
    $positions->day = [];
    $holdings = [];
}

// Process positions
$positionsDay = $positions->day ?? [];
$dayPositions = [];
$dayPositionsKeys = [];

foreach ($positionsDay as $index => $position) {
    $tradingSymbol = $position->tradingsymbol;
    $positionObj = new stdClass();
    $positionObj->trading_symbol = $tradingSymbol;
    $positionObj->quantity = $position->quantity;
    
    $dayPositions[] = $positionObj;
    $dayPositionsKeys[$tradingSymbol] = $index;
}

// Get trading symbols and quotes
$tradingData = getTradingSymbols($holdings);
$tradingSymbols = $tradingData['trading_symbols'];
$quoteSymbols = $tradingData['quote_symbols'];
$holdingKeys = $tradingData['holding_keys'];

// Add Nifty 50 to quotes
$nifty50Quote = "NSE:NIFTY 50";
$quoteSymbols[] = $nifty50Quote;

// Get LTP data
try {
    $ltps = $kite->getLTP($quoteSymbols);
} catch (Exception $e) {
    error_log("Failed to fetch LTP data: " . $e->getMessage());
    $ltps = new stdClass();
}

// Update holding quantities with day positions
foreach($tradingSymbols as $ts) {
    $holdingQty = $holdings[$holdingKeys[$ts]]->opening_quantity;
    if(isset($dayPositionsKeys[$ts]))
    {
        $key = $dayPositionsKeys[$ts];
        $dhq = $dayPositions[$key]->quantity;
        $holdingQty += intval($dhq);
    }
    $holdings[$holdingKeys[$ts]]->holding_quantity = $holdingQty;
}

// Calculate target value if not set
$nifty50Ltp = $ltps->$nifty50Quote->last_price ?? 0;
$maxCurrentValue = 0.0;

if ($targetValue === 0.0) {
    foreach ($tradingSymbols as $symbol) {
        if (in_array($symbol, ["SETFNIF50", "NIFTYBEES"])) {
            continue;
        }
        
        $quoteSymbol = "NSE:" . $symbol;
        $ltp = (float)($ltps->$quoteSymbol->last_price ?? 0);
        $holdingQty = $holdings[$holdingKeys[$symbol]]->holding_quantity ?? 0;
        $currentValue = (float)((int)$holdingQty * $ltp);
        
        $maxCurrentValue = max($maxCurrentValue, $currentValue);
    }
    $targetValue = $maxCurrentValue;
} else {
    $maxCurrentValue = $targetValue;
}

// Process trading data
$result = [];
$totalBuyAmount = 0.0;

foreach ($tradingSymbols as $symbol) {
    if (in_array($symbol, ["SETFNIF50", "NIFTYBEES"])) {
        continue;
    }
    
    $quoteSymbol = "NSE:" . $symbol;
    $ltpObj = $ltps->$quoteSymbol ?? new stdClass();
    
    $obj = new stdClass();
    $obj->trading_symbol = $symbol;
    $obj->quote_symbol = $quoteSymbol;
    $obj->instrument_token = $ltpObj->instrument_token ?? '';
    
    $openingQty = $holdings[$holdingKeys[$symbol]]->opening_quantity ?? 0;
    $holdingQty = $holdings[$holdingKeys[$symbol]]->holding_quantity ?? 0;
    
    $obj->opening_quantity = $openingQty;
    $obj->holding_quantity = $holdingQty;
    
    $ltp = (float)($ltpObj->last_price ?? 0);
    $obj->ltp = formatNumber($ltp);
    
    $currentValue = (int)$holdingQty * $ltp;
    $obj->current_value = formatNumber($currentValue);
    
    $difference = $targetValue - $currentValue;
    $obj->difference = formatNumber($difference);
    
    $buyQty = 0;
    if ($difference > 0.0) {
        $buyQty = floor($difference / $ltp);
    }
    
    $obj->buy_qty = $buyQty;
    $buyAmount = $buyQty * $ltp;
    $obj->buy_amt = formatNumber($buyAmount);
    $totalBuyAmount += $buyAmount;
    
    $obj->proposed_value = formatNumber($currentValue + $buyAmount);
    $result[$symbol] = $obj;
    
    if ($currentValue === $maxCurrentValue) {
        $obj->trading_symbol = "*" . $symbol;
    }
}

// Include the template
require __DIR__ . '/../templates/index.php'; 