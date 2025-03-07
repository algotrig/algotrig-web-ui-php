<?php

require_once __DIR__ . '/config.php';

function loadAppConfig(string $iniFile): array
{
    try {
        return loadConfig($iniFile);
    } catch (RuntimeException $e) {
        die("Configuration Error: " . $e->getMessage());
    }
}

function setAppEnvironment(array $config)
{
    // Set error reporting based on environment
    error_reporting($config['app']['debug'] ? intval($config['app']['error_reporting']) : 0);
    ini_set('display_errors', $config['app']['debug'] ? '1' : '0');

    // Set timezone
    date_default_timezone_set($config['app']['timezone']);
}
