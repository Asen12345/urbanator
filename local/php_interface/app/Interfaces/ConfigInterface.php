<?php

namespace App\Interfaces;

interface ConfigInterface
{
    public function get(string $key, $default = null);
}