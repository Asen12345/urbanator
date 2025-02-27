<?php

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');

header('Access-Control-Max-Age: 86400');

if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
	exit();
}

$start = microtime(true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$end = microtime(true);
$prolog_time = round($end - $start, 4);
header('Server-Timing:core;dur=' . $prolog_time, false);

while (ob_end_clean()) {
}

if (!\CModule::IncludeModule("catalog")) {
	throw new \Exception("Модуль catalog не установлен");
}

$loader = require($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/vendor/autoload.php');

$loader->addPsr4('App\\', $_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/app');

$dispatcher = require_once __DIR__ . "/routes.php";

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$uri = substr($uri, 4);

if (false !== $pos = strpos($uri, '?')) {
	$uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

$response = \Bitrix\Main\Context::getCurrent()->getResponse();

switch ($routeInfo[0]) {
	case FastRoute\Dispatcher::NOT_FOUND:
		$response->setStatus("404 Not Found");
		break;
	case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
		$allowedMethods = $routeInfo[1];

		$response->setStatus("405 Method Not Allowed");
		break;
	case FastRoute\Dispatcher::FOUND:
		$response->setStatus("200 OK");

		$handler = $routeInfo[1];
		$vars = $routeInfo[2];

		$start2 = microtime(true);

		try {
			if (is_array($handler)) {
				$ob = new $handler[0];
				$result = call_user_func_array([$ob, $handler[1]], $vars);
			} else {
				$result = call_user_func_array($handler, $vars);
			}
			if ($result instanceof \Bitrix\Main\Response) {
				\Bitrix\Main\Context::getCurrent()->setResponse($result);
				$response = $result;
			} else {
				$response_data = [
					"status" => "ok",
					"data" => $result,
				];

				$end = microtime(true);
				$func_time = round($end - $start2, 4);

				header("Server-Timing:func_{$handler[1]};dur=" . $func_time, false);

				$response->addHeader("content-type", "application/json");
				$response_data = json_encode($response_data, JSON_UNESCAPED_UNICODE);
				$response->setContent($response_data);
			}
		} catch (\Exception $e) {
			$response_data = json_encode([
				"status" => "error",
				"error" => $e->getMessage(),
				"code" => $e->getCode(),
			], JSON_UNESCAPED_UNICODE);
			$response->addHeader("content-type", "application/json");
			$response->setContent($response_data);
		}

		break;
}

$end = microtime(true);
$total_time = round($end - $start, 4);
header('Server-Timing:total_index;dur=' . $total_time, false);
$response->send();
