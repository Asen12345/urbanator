<?php

namespace App\Config;

use App\Interfaces\ConfigInterface;

class ConfigLoader implements ConfigInterface
{
    private array $config;

    public function __construct(string $configPath = null)
    {
        $this->config = include $configPath ?? __DIR__ . '/config.php';
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
