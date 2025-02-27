<?php

use App\Controllers\CategoryController;
use FastRoute\RouteCollector;

return \FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addGroup('/categories', function (RouteCollector $r) {
        $r->addRoute('GET', '', [CategoryController::class, 'list']);
        $r->addRoute('GET', '/subcategories', [CategoryController::class, 'subcategories']);
    });
});
