<?php

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Обрабатывает проверку токена.
     * Извлекает токен из заголовка Authorization,
     * затем ищет пользователя по полю UF_API_TOKEN.
     *
     * @return int ID пользователя, если токен валиден.
     * @throws \Exception если токен отсутствует или недействителен.
     */
    public function handle(): int
    {
        // Получаем все HTTP-заголовки
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : getallheaders();
        
        if (!isset($headers['Authorization'])) {
            throw new \Exception("Не передан заголовок Authorization", 401);
        }
        
        // Заголовок должен быть в формате: "Bearer <token>"
        $authHeader = $headers['Authorization'];
        $parts = explode(' ', $authHeader);
        
        if (count($parts) !== 2 || strtolower($parts[0]) !== 'bearer') {
            throw new \Exception("Неверный формат заголовка Authorization", 401);
        }
        
        $token = trim($parts[1]);
        if (empty($token)) {
            throw new \Exception("Пустой токен", 401);
        }
        
        // Ищем пользователя по токену, используя Bitrix API
        $userResult = \CUser::GetList(
            $by = "id", 
            $order = "asc", 
            ["UF_API_TOKEN" => $token]
        );
        
        if ($user = $userResult->Fetch()) {
            // Если найден пользователь, возвращаем его ID
            return (int)$user['ID'];
        }
        
        throw new \Exception("Неверный или недействительный токен", 401);
    }
}