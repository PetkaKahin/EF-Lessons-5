<?php

declare(strict_types=1);

use Infrastructure\Http\Controllers\EchoController;
use Infrastructure\Http\Controllers\HeadersController;
use Infrastructure\Http\Controllers\HealthController;
use Infrastructure\Http\Controllers\WebhookReceiverController;
use Infrastructure\Kernel\Router;

return static function (
    Router $router,
) {
    $router->get('/health', [HealthController::class, '__invoke']);
    $router->get('/headers', [HeadersController::class, '__invoke']);
    $router->post('/echo', [EchoController::class, '__invoke']);
    $router->post('/webhook-receiver', [WebhookReceiverController::class, '__invoke']);
};
