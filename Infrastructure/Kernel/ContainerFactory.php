<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Application\UseCases\Webhook\RetryWebhookAttemptsUseCase;
use Infrastructure\Config\Config;
use Infrastructure\Config\Globals;
use Infrastructure\DataBase\MigrationRunner;
use Infrastructure\DataBase\PdoTransactionManager;
use Infrastructure\DataBase\Repositories\IdempotencyRepository;
use Infrastructure\DataBase\Repositories\WebhookAttemptRepository;
use Infrastructure\Http\Client\WebhookClient;
use Infrastructure\Http\Controllers\EchoController;
use Infrastructure\Http\Controllers\HeadersController;
use Infrastructure\Http\Controllers\HealthController;
use Infrastructure\Http\Controllers\WebhookReceiverController;
use Infrastructure\Http\Middleware\AuthMiddleware;
use Infrastructure\Http\Middleware\CorsMiddleware;
use Infrastructure\Http\Middleware\GlobalMiddlewareRegistry;
use Infrastructure\Http\Middleware\MiddlewarePipeline;
use PDO;

final class ContainerFactory
{
    public function create(): Container
    {
        $container = new Container();

        $container->set(Config::class, static fn (): Config => new Config());
        $container->set(EchoController::class, static fn (): EchoController => new EchoController());
        $container->set(HeadersController::class, static fn (): HeadersController => new HeadersController());
        $container->set(HealthController::class, static fn (): HealthController => new HealthController());
        $container->set(WebhookReceiverController::class, static fn (): WebhookReceiverController => new WebhookReceiverController());
        $container->set(GlobalMiddlewareRegistry::class, static fn (): GlobalMiddlewareRegistry => new GlobalMiddlewareRegistry());
        $container->set(MiddlewarePipeline::class, static fn (Container $container): MiddlewarePipeline => new MiddlewarePipeline(
            $container,
            $container->get(GlobalMiddlewareRegistry::class),
        ));
        $container->set(Router::class, static fn (Container $container): Router => new Router(
            $container,
            $container->get(MiddlewarePipeline::class),
        ));
        $container->set(CorsMiddleware::class, static fn (Container $container): CorsMiddleware => new CorsMiddleware(
            $container->get(Config::class),
        ));
        $container->set(AuthMiddleware::class, static fn (Container $container): AuthMiddleware => new AuthMiddleware(
            $container->get(Config::class),
        ));

        $container->set(PDO::class, static function (Container $container): PDO {
            /** @var Config $config */
            $config = $container->get(Config::class);

            return new PDO(
                (string) $config->get(Globals::DATABASE_DSN_NAME),
                (string) $config->get(Globals::DATABASE_USER_NAME),
                (string) $config->get(Globals::DATABASE_PASSWORD_NAME),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
        });

        $container->set(IdempotencyRepository::class, static fn (Container $container): IdempotencyRepository => new IdempotencyRepository(
            $container->get(PDO::class),
        ));

        $container->set(PdoTransactionManager::class, static fn (Container $container): PdoTransactionManager => new PdoTransactionManager(
            $container->get(PDO::class),
        ));

        $container->set(WebhookAttemptRepository::class, static fn (Container $container): WebhookAttemptRepository => new WebhookAttemptRepository(
            $container->get(PDO::class),
        ));

        $container->set(WebhookClient::class, static fn (): WebhookClient => new WebhookClient());

        $container->set(MigrationRunner::class, static fn (Container $container): MigrationRunner => new MigrationRunner(
            $container->get(PDO::class),
        ));

        $container->set(RetryWebhookAttemptsUseCase::class, static fn (Container $container): RetryWebhookAttemptsUseCase => new RetryWebhookAttemptsUseCase(
            $container->get(WebhookClient::class),
            $container->get(WebhookAttemptRepository::class),
            (string) $container->get(Config::class)->get(Globals::WEBHOOK_URL_NAME),
        ));

        return $container;
    }
}
