<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Csrf\Guard;
use App\Application\Middleware\CsrfHeaderBridgeMiddleware;

require __DIR__ . '/../../vendor/autoload.php';

// .env laden (DB-Zugangsdaten etc.)
if (file_exists(__DIR__ . '/../../.env')) {
	Dotenv\Dotenv::createImmutable(__DIR__ . '/../../')->load();
}

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/dependencies.php');
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$csrf = new Guard($app->getResponseFactory(), persistentTokenMode: true);
$app->add($csrf);
$app->add(new CsrfHeaderBridgeMiddleware($csrf));
$container->set(Guard::class, $csrf);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

(require __DIR__ . '/routes.php')($app);

return $app;