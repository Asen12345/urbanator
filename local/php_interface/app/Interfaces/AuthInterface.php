<?php

namespace App\Interfaces;

interface AuthInterface
{
    public function loginUser(array $data): string;
    public function registerUser(array $data): string;
}
