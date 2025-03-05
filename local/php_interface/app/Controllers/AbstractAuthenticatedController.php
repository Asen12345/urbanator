<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;

abstract class AbstractAuthenticatedController extends AbstractController
{
    protected AuthMiddleware $authMiddleware;
    protected int $userId;

    public function __construct(AuthMiddleware $authMiddleware)
    {
        $this->authMiddleware = $authMiddleware;
    }

    /**
     * Проверка аутентификации перед выполнением действия
     */
    protected function authenticate(): int
    {
        if (!isset($this->userId)) {
            $this->userId = $this->authMiddleware->handle();
        }
        return $this->userId;
    }
}
