<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['app']['name'] . " - " . $config['app']['env']); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tablesort/5.2.1/tablesort.min.js"></script>
</head>

<body>
    <header>
        <div class="header-content">
            <div class="time-info">
                Current time: <?php echo date('d-m-Y H:i:s A'); ?>
            </div>
            <div class="refresh-info">
                Refresh: <?php echo $refreshInterval; ?> seconds
            </div>
            <div class="actions">
                <a href="/logout.php" class="btn btn-danger">Logout</a>
                <a href="/?execute_orders=0&r=<?php echo $refreshInterval; ?>" class="btn btn-primary">Refresh</a>
                <a href="/?execute_orders=0&target_value=<?php echo $targetValue; ?>&r=<?php echo $refreshInterval; ?>" class="btn btn-secondary">Refresh [TV]</a>
                <a href="/?execute_orders=1&target_value=<?php echo $targetValue; ?>&r=<?php echo $refreshInterval; ?>" class="btn btn-success">Execute</a>
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
        </div>

        <div class="trading-table">
            <table id="trading_table">
                <thead>
                    <?php
                    $firstRow = reset($tradingData);
                    echo objectToTableRow($firstRow, true);
                    ?>
                </thead>
                <tbody>
                    <?php echo $zerodhaKite->getTbody(); ?>
                </tbody>
            </table>
            <script type="text/javascript">
                new Tablesort(document.getElementById('trading_table'));
            </script>
        </div>

        <?php if ($executeOrders > 0): ?>
            <div class="order-execution">
                <h3>Executed Orders:</h3>
                <pre>
                <?php
                    print_r($zerodhaKite->getExecutedOrdersData());
                    print_r($zerodhaKite->getFailedOrders());
                ?>
                </pre>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y') . " " . htmlspecialchars($config['app']['name']); ?>. All rights reserved.</p>
    </footer>
</body>

</html>