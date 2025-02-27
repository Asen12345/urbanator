<?php

namespace App\Config;

class ConfigLoader
{
    private array $config;

    public function __construct()
    {
        $this->config = include __DIR__ . '/config.php';
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}