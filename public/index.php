<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/config_loader.php';
require_once __DIR__ . '/../src/AlgoTrig/ZerodhaKite.php';

use AlgoTrig\ZerodhaKite;

// Load configuration
$config = loadAppConfig(__DIR__ . '/../algotrig.ini');

// Set application environment
setAppEnvironment($config);

session_start();

// Check authentication
if (!isset($_SESSION['access_token'])) {
    header('Location: /logout.php');
    exit;
}

// Validate and sanitize input
$refreshInterval = validateRefreshInterval(
    isset($_GET['r']) ? (int)$_GET['r'] : intval($config['refresh']['default_interval']), $config
);
$targetValue = isset($_GET['target_value']) ? (float)$_GET['target_value'] : 0.0;
$executeOrders = isset($_GET['execute_orders']) ? (int)$_GET['execute_orders'] : 0;

// Set refresh header
header("Refresh: {$refreshInterval}");

$zerodhaKite = new ZerodhaKite($config['zerodha']);
$zerodhaKite->initializeKite($_SESSION['access_token']);
$zerodhaKite->process($targetValue, $executeOrders);

$nifty50Quote = $config['zerodha']['stock_exchange_key'] . ":NIFTY 50";
$nifty50Ltp = $zerodhaKite->fetchLTPforQuoteSymbol($nifty50Quote);

$tradingData = $zerodhaKite->getTradingData();
$totalBuyAmount = $zerodhaKite->getTotalBuyAmount();

// Include the template
require __DIR__ . '/../templates/index.php'; 