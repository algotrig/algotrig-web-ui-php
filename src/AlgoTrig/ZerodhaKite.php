<?php

declare(strict_types=1);

namespace AlgoTrig;

use KiteConnect\KiteConnect;
use stdClass;
use Exception;

class ZerodhaKite {
    private KiteConnect $kite;
    private array $config;
    private string $stockExchangeKey;
    private array $kiteHoldings;
    private mixed $kitePositions;
    private mixed $kiteLtps;
    private array $kiteOrders;
    private array $tradingSymbols;
    private array $quoteSymbols;
    private array $holdingKeys;
    private array $dayPositionsObjects;
    private float $totalBuyAmount;
    private float $targetValue;
    private float $maxCurrentValue;
    private array $tradingData;
    private array $executedOrders;
    private array $executedOrdersData;
    private array $failedOrders;

    public function __construct(array $config) {
        $this->config = $config;
        $this->stockExchangeKey = $config['stock_exchange_key'];
        $this->dayPositionsObjects = [];
        $this->holdingKeys = [];
        $this->kiteOrders = [];
        $this->tradingSymbols = [];
        $this->quoteSymbols = [];
        $this->maxCurrentValue = 0.0;
        $this->targetValue = 0.0;
        $this->executedOrders = [];
        $this->executedOrdersData = [];
        $this->failedOrders = [];
    }

    function initializeKite($accessToken) {
        // Initialize KiteConnect
        try {
            $this->kite = new KiteConnect(
                $this->config['api_key'],
                $accessToken
            );
        } catch (Exception $e) {
            error_log("KiteConnect initialization failed: " . $e->getMessage());
            header('Location: /logout.php');
            exit;
        }
    }

    function process($targetValue = 0.0, $executeOrders = 0) {
        // Fetch kiteHoldings
        $this->fetchHoldings();

        // Fetch kitePositions
        $this->fetchPositions();

        // Process day kitePositions
        $this->processDayPositions();

        // Process symbols
        $this->processSymbols();

        // Add Nifty 50 to quotes
        $nifty50Quote = $this->stockExchangeKey . ":NIFTY 50";
        $this->addQuoteSymbol($nifty50Quote);

        // Fetch LTP data
        $this->fetchLTPData();

        // Update holding quantities
        $this->updateHoldingQuantities();

        // Calculate max current value
        $this->calculateMaxCurrentValue();

        // Calculate target value if not set
        $targetValue = $targetValue === 0.0 ? $this->getMaxCurrentValue() : $targetValue;
        $this->setTargetValue($targetValue);

        // Process trading data
        $this->processTradingData();

        // Execute orders
        if ($executeOrders === 1) {
            $this->executeOrders();
        }
    }

    /**
     * Fetch kiteHoldings from Kite
     */
    function fetchHoldings() {
        try {
            $this->kiteHoldings = $this->kite->getHoldings();
        } catch (Exception $e) {
            error_log("Failed to fetch kiteHoldings: " . $e->getMessage());
            trigger_error("Triggered error: Failed to fetch kiteHoldings: " . $e->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Fetch kitePositions from Kite
     */
    function fetchPositions() {
        try {
            $this->kitePositions = $this->kite->getPositions();
        } catch (Exception $e) {
            error_log("Failed to fetch kitePositions: " . $e->getMessage());
            trigger_error("Triggered error: Failed to fetch kitePositions: " . $e->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Process day kitePositions
     */
    function processDayPositions() {
        // Process kitePositions
        foreach ($this->kitePositions->day as $index => $position) {
            $this->dayPositionsObjects[$position->tradingsymbol] = $position;
        }
    }

    /**
     * Get trading symbols from kiteHoldings
     *
     * @return array{ trading_symbols: array, quote_symbols: array, holding_keys: array }
     */
    function processSymbols() {
        foreach ($this->kiteHoldings as $index => $holding) {
            $this->tradingSymbols[] = $holding->tradingsymbol;
            $this->quoteSymbols[] = $this->stockExchangeKey . ":" . $holding->tradingsymbol;
            $this->holdingKeys[$holding->tradingsymbol] = $index;
        }
    }

    function addQuoteSymbol($quoteSymbol) {
        $this->quoteSymbols[] = $quoteSymbol;
    }

    /**
     * Update holding quantities with day kitePositions
     */
    function updateHoldingQuantities() {
        // Update holding quantities with day kitePositions
        foreach ($this->tradingSymbols as $ts) {
            $holdingQty = $this->kiteHoldings[$this->holdingKeys[$ts]]->opening_quantity;
            if (isset($this->dayPositionsObjects[$ts])) {
                $holdingQty += intval($this->dayPositionsObjects[$ts]->quantity);
            }
            $this->kiteHoldings[$this->holdingKeys[$ts]]->holding_quantity = $holdingQty;
        }
    }

    /**
     * Fetch LTP data from Kite
     */
    function fetchLTPData() {
        // Get LTP data
        try {
            $this->kiteLtps = $this->kite->getLTP($this->quoteSymbols);
        } catch (Exception $e) {
            error_log("Failed to fetch LTP data: " . $e->getMessage());
            trigger_error("Triggered error: Failed to fetch LTP data: " . $e->getMessage(), E_USER_ERROR);
        }
    }

    function fetchLTPforQuoteSymbol($quoteSymbol) {
        $ltp = (float)($this->kiteLtps->$quoteSymbol->last_price ?? 0);
        return $ltp;
    }

    /**
     * Get target value
     */
    function calculateMaxCurrentValue() {
        foreach ($this->tradingSymbols as $symbol) {
            if ($this->shouldSkipSymbol($symbol)) {
                continue;
            }

            $quoteSymbol = $this->getQuoteSymbol($symbol);
            $ltp = (float)($this->kiteLtps->$quoteSymbol->last_price ?? 0);
            $holdingQty = $this->kiteHoldings[$this->holdingKeys[$symbol]]->holding_quantity ?? 0;
            $currentValue = (float)((int)$holdingQty * $ltp);

            $this->maxCurrentValue = max($this->maxCurrentValue, $currentValue);
        }
    }

    /**
     * Get quote symbol for a trading symbol
     */
    function getQuoteSymbol($tradingSymbol) {
        return $this->stockExchangeKey . ":" . $tradingSymbol;
    }

    /**
     * Process trading data for each symbol
     */
    function processTradingData() {
        $this->totalBuyAmount = 0.0;

        foreach ($this->tradingSymbols as $symbol) {
            if ($this->shouldSkipSymbol($symbol)) {
                continue;
            }

            $quoteSymbol = $this->getQuoteSymbol($symbol);
            $ltpObj = $this->kiteLtps->$quoteSymbol;

            $obj = new stdClass();
            $obj->trading_symbol = $symbol;
            $obj->quote_symbol = $quoteSymbol;
            $obj->instrument_token = $ltpObj->instrument_token;

            $openingQty = $this->kiteHoldings[$this->holdingKeys[$symbol]]->opening_quantity;
            $holdingQty = $this->kiteHoldings[$this->holdingKeys[$symbol]]->holding_quantity;
            $obj->opening_quantity = $openingQty;
            $obj->holding_quantity = $holdingQty;

            $ltp = floatval($ltpObj->last_price);
            $obj->ltp = number_format($ltp, 2, '.', '');

            $currentValue = intval($holdingQty) * $ltp;
            $obj->current_value = number_format($currentValue, 2, '.', '');

            $difference = $this->targetValue - $currentValue;
            $obj->difference = number_format($difference, 2, '.', '');

            $buyQty = $difference > 0.0 ? floor($difference / $ltp) : 0.0;

            $obj->buy_qty = $buyQty;
            $buyAmount = $buyQty * $ltp;
            $obj->buy_amt = number_format($buyAmount, 2, '.', '');
            $this->totalBuyAmount += $buyAmount;

            $obj->proposed_value = number_format($currentValue + $buyAmount, 2, '.', '');

            if ($currentValue === $this->maxCurrentValue) {
                $obj->trading_symbol = "*" . $symbol;
            }

            $this->tradingData[$symbol] = $obj;
            if ($obj->buy_qty > 0) {
                $this->kiteOrders[] = $this->getOrder($obj);
            }
        }
    }

    /**
     * Execute orders
     */
    function executeOrders() {
        foreach ($this->kiteOrders as $order) {
            try {
                $executedOrdersData = $this->kite->placeOrder("regular", $order);
                $this->executedOrders[] = $order;
                $this->executedOrdersData[] = $executedOrdersData;
            } catch (Exception $e) {
                error_log("Error executing order: " . $e->getMessage());
                $order['error'] = $e;
                $this->failedOrders[] = $order;
            }
        }
    }

    /**
     * Get tradingData
     */
    function getTradingData() {
        return $this->tradingData;
    }

    /**
     * Get executedOrdersData
     */
    function getExecutedOrdersData() {
        return $this->executedOrdersData;
    }

    /**
     * Check if a symbol should be skipped in processing
     * 
     * @param string $symbol Trading symbol to check
     * @return bool True if symbol should be skipped
     */
    private function shouldSkipSymbol(string $symbol): bool {
        return in_array($symbol, ["SETFNIF50", "NIFTYBEES", "LIQUIDBEES"]);
    }
      
    /**
     * Generate order data for a trading symbol
     *
     * @param object $obj The trading object
     * @return array Order data
     */
    function getOrder(object $obj): array {
        $isSpecialSymbol = in_array($obj->trading_symbol, ["FMCGIETF", "HDFCSENSEX"]);

        if ($isSpecialSymbol) {
            $quoteSymbols = [$obj->quote_symbol];
            $quotes = $this->kite->getQuote($quoteSymbols);
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
     * Get tbody html for tradingData
     */
    function getTbody(){
        $targetValue = $this->getTargetValue();
        $tbody = "";
        foreach ($this->tradingData as $symbol => $row) {
            if (floatval($row->difference) >= 0.0 || floatval($row->current_value) == $targetValue) {
                $tbody .= objectToTableRow($row);
            }
        }
        return $tbody;
    }


    /**
     * Get maxCurrentValue
     */
    function getMaxCurrentValue(): float {
        return $this->maxCurrentValue;
    }

    /**
     * Set maxCurrentValue
     */
    function setMaxCurrentValue(float $maxCurrentValue) {
        $this->maxCurrentValue = $maxCurrentValue;
    }

    /**
     * Get targetValue
     */
    function getTargetValue(): float {
        return $this->targetValue;
    }

    /**
     * Set targetValue
     */
    function setTargetValue(float $targetValue) {
        $this->targetValue = $targetValue;
    }

    /**
     * Get totalBuyAmount
     */
    function getTotalBuyAmount(): float {
        return $this->totalBuyAmount;
    }

    /**
     * Get failedOrders
     */
    function getFailedOrders(){
        return $this->failedOrders;
    }
}
