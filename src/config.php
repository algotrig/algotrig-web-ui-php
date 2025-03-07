<?php

declare(strict_types=1);

/**
 * Load configuration from INI file
 *
 * @param string $iniFile Path to the INI file
 * @return array Configuration array
 * @throws RuntimeException If INI file is missing or required values are not set
 */
function loadConfig(string $iniFile): array
{
    // Check if INI file exists
    if (!file_exists($iniFile)) {
        throw new RuntimeException("Configuration file '{$iniFile}' not found");
    }

    // Load INI configuration
    $iniConfig = parse_ini_file($iniFile, true);
    if ($iniConfig === false) {
        throw new RuntimeException("Failed to parse configuration file '{$iniFile}'");
    }

    // Default configuration
    $config = [
        'app' => [
            'name' => 'AlgoTrig',
            'version' => '0.1.0',
            'env' => 'LOCAL',
            'timezone' => 'Asia/Kolkata',
        ],
    ];

    // Merge INI configuration with defaults
    $config = array_replace_recursive($config, $iniConfig);

    // Validate required configuration values
    $requiredValues = [
        'zerodha.api_key' => 'Zerodha API Key',
        'zerodha.secret' => 'Zerodha Secret Key'
    ];

    foreach ($requiredValues as $path => $name) {
        $keys = explode('.', $path);
        $value = $config;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                throw new RuntimeException("Required configuration value '{$name}' is missing in {$iniFile}");
            }
            $value = $value[$key];
        }

        if (empty($value)) {
            throw new RuntimeException("Required configuration value '{$name}' cannot be empty in {$iniFile}");
        }
    }

    return $config;
} 