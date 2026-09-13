<?php

declare(strict_types=1);

use App\Application\Middleware\LocaleMiddleware;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Csrf\Guard;
use App\Application\Middleware\CsrfHeaderBridgeMiddleware;
use Symfony\Component\Translation\Translator;

require __DIR__ . '/../../vendor/autoload.php';

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
$app->add(new LocaleMiddleware($container->get(Translator::class)));

$container->set(Guard::class, $csrf);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

(require __DIR__ . '/routes.php')($app);

return $app;