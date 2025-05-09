<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['app']['name'] . " - " . $config['app']['env']); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon.ico">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.32.0/js/jquery.tablesorter.min.js"></script>
    <script src="/assets/js/jsonview.js"></script>
</head>

<body>
    <header>
        <div class="header-content">
            <div class="header-logo">
                <span class="algo">Algo</span><span class="trig">Trig</span><span class="host-suffix"><?php echo $config['app']['host_suffix']; ?></span>
            </div>
            <div class="actions">
                <a href="/?execute_orders=1&target_value=<?php echo $targetValue; ?>&r=<?php echo $refreshInterval; ?>" class="btn btn-success">Execute</a>
                <a href="/?execute_orders=0&r=<?php echo $refreshInterval; ?>" class="btn btn-primary">Refresh</a>
                <a href="/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </header>

    <main>
        <div class="market-info">
            <h2>Nifty 50:
                <a href="/?execute_orders=0&target_value=<?php echo $nifty50Ltp; ?>&r=<?php echo $refreshInterval; ?>">
                    <?php echo formatNumber($nifty50Ltp); ?>
                </a>
            </h2>
            <div class="time-info">
                Executed at: <span class="font-bold"><?php echo date('d-M-Y H:i:s A'); ?></span>
                <br />
                Refresh Interval: <span class="font-bold"><?php echo $refreshInterval; ?> seconds</span>
            </div>
        </div>
        <div class="summary">
            <table>
                <tr>
                    <td>Max Current Value:</td>
                    <td class="numeric-value"><?php echo getAnchor(formatNumber($zerodhaKite->getMaxCurrentValue())); ?></td>
                </tr>
                <tr>
                    <td>Target Value:</td>
                    <td class="numeric-value"><?php echo getAnchor(formatNumber($targetValue)); ?></td>
                </tr>
                <tr>
                    <td>Total Buy Amount:</td>
                    <td class="numeric-value"><?php echo formatNumber($totalBuyAmount); ?></td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>Opening Balance:</td>
                    <td class="numeric-value"><?php echo formatNumber($margins->equity->available->opening_balance); ?></td>
                </tr>
                <tr>
                    <td>Debits:</td>
                    <td class="numeric-value"><?php echo formatNumber($margins->equity->utilised->debits); ?></td>
                </tr>
                <tr>
                    <td>Sales:</td>
                    <td class="numeric-value"><?php echo formatNumber($margins->equity->utilised->holding_sales); ?></td>
                </tr>
                <tr>
                    <td>Payin:</td>
                    <td class="numeric-value"><?php echo formatNumber($margins->equity->available->intraday_payin); ?></td>
                </tr>
                <tr>
                    <td>Live Balance:</td>
                    <td class="numeric-value"><?php echo formatNumber($margins->equity->available->live_balance); ?></td>
                </tr>
            </table>
        </div>
        <?php if (!empty($action) || $executeOrders > 0) { ?>
            <div id="orders_div">
                <?php if ($action == "submit-trade") { ?>
                    <div class="json-data">
                        <h4>Submitted Orders:</h4>
                        <div id="submit_trade_data"></div>
                        <script>
                            var submitTradeJsonData = <?php echo json_encode($submitTradeData); ?>;
                            var submitTradeTree = jsonview.renderJSON(submitTradeJsonData, document.querySelector('#submit_trade_data'));
                            console.log(submitTradeTree);
                        </script>
                    </div>
                <?php } ?>
                <?php if ($executeOrders > 0) { ?>
                    <div class="json-data">
                        <h4>Executed Orders:</h4>
                        <div id="executed_orders_data"></div>
                        <script>
                            var executedOrdersJsonData = <?php echo json_encode($zerodhaKite->getExecutedOrdersData()); ?>;
                            var executedOrdersTree = jsonview.renderJSON(executedOrdersJsonData, document.querySelector('#executed_orders_data'));
                            console.log(executedOrdersTree);
                        </script>
                    </div>
                    <div class="json-data">
                        <h4>Failed Orders:</h4>
                        <div id="failed_orders_data"></div>
                        <script>
                            var failedOrdersJsonData = <?php echo json_encode($zerodhaKite->getFailedOrders());?>;
                            var failedOrdersTree = jsonview.renderJSON(failedOrdersJsonData, document.querySelector('#failed_orders_data'));
                            console.log(failedOrdersTree);
                        </script>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="trading-table">
            <table id="trading_table">
                <?php
                $firstRow = reset($tradingData);
                echo objectToTableHeader($firstRow, true);
                echo getTbody($zerodhaKite);
                ?>
            </table>
            <script type="text/javascript">
                $(function() {
                    $("#trading_table").tablesorter();
                });
            </script>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y') . " " . htmlspecialchars($config['app']['name']); ?>. All rights reserved.</p>
    </footer>
    <script>
        document.querySelectorAll('.quantity').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.value < 1) {
                    this.value = '';
                }
            });
        });
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const symbol = form.querySelector('input[name="tradingSymbol"]').value;
                const quantity = form.querySelector('input[name="quantity"]').value;

                const message = `Are you sure you want to ${form.querySelector('[name="action"]:focus')?.value?.toUpperCase()} ${quantity} ${symbol}?`;

                if (!confirm(message)) {
                    e.preventDefault(); // Stop form submission
                }
            });
        });
    </script>
</body>

</html>