<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/config_loader.php';

use AlgoTrig\PhpCore\ZerodhaKite;

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

$refreshInterval = isset($_GET['r']) ? validateRefreshInterval((int)$_GET['r'], $config) : intval($config['refresh']['default_interval']);
$targetValue = isset($_GET['target_value']) ? (float)$_GET['target_value'] : 0.0;
$executeOrders = isset($_GET['execute_orders']) ? (int)$_GET['execute_orders'] : 0;

// Set refresh header
header("Refresh: {$refreshInterval}");

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    $zerodhaKite = new ZerodhaKite($config['zerodha'],$_SESSION['access_token']);

    if (!empty($action)) {
        if($action == "submit-trade"){
            $submitTradeData = submitTrade($zerodhaKite);
        }
    }

    $zerodhaKite->process($targetValue);
    // Execute Orders
    if ($executeOrders === 1) {
        $zerodhaKite->executeOrders();
    }

    $margins = $zerodhaKite->fetchMargins("equity");

    $nifty50Ltp = $zerodhaKite->getLTPforTradingSymbol("NIFTY 50");

    $tradingData = $zerodhaKite->getTradingData();
    $totalBuyAmount = $zerodhaKite->getTotalBuyAmount();
} catch (Exception $e) {
    die("Something went wrong: " . $e->getMessage());
}

// Include the template
require __DIR__ . '/../templates/index.php';
