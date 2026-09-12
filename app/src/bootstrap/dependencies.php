<?php

declare(strict_types=1);

use App\Application\Service\AuthorizationService;
use App\Domain\Repository\ActionRepositoryInterface;
use App\Domain\Repository\IssueRepositoryInterface;
use App\Domain\Repository\RoleRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\DoctrineActionRepository;
use App\Infrastructure\Doctrine\Repository\DoctrineIssueRepository;
use App\Infrastructure\Doctrine\Repository\DoctrineRoleRepository;
use App\Infrastructure\Doctrine\Repository\DoctrineUserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Views\Twig;

return [
	'settings' => fn() => require __DIR__ . '/settings.php',

	ResponseFactoryInterface::class => fn() => new ResponseFactory(),

	EntityManagerInterface::class => function (ContainerInterface $c) {
		$settings = $c->get('settings')['doctrine'];
		$dbParams = $c->get('settings')['db'];

		$config = ORMSetup::createAttributeMetadataConfiguration(
			paths: [$settings['entity_dir']],
			isDevMode: $settings['is_dev_mode'],
		);

		return new EntityManager(
			\Doctrine\DBAL\DriverManager::getConnection($dbParams, $config),
			$config
		);
	},

		// --- Repository-Bindings ------------------------------------------
		// Genau HIER wird entschieden, welche Persistenz-Implementierung
		// hinter jedem Domain-Interface steckt. Ein DB-Wechsel (z.B. auf
		// MongoDB) bedeutet: neue Implementierung schreiben und nur diese
		// Zeilen hier anpassen -- der Rest der Anwendung bleibt unberuehrt.
	IssueRepositoryInterface::class => fn(ContainerInterface $c) =>
		new DoctrineIssueRepository($c->get(EntityManagerInterface::class)),

	UserRepositoryInterface::class => fn(ContainerInterface $c) =>
		new DoctrineUserRepository($c->get(EntityManagerInterface::class)),

	RoleRepositoryInterface::class => fn(ContainerInterface $c) =>
		new DoctrineRoleRepository($c->get(EntityManagerInterface::class)),

	ActionRepositoryInterface::class => fn(ContainerInterface $c) =>
		new DoctrineActionRepository($c->get(EntityManagerInterface::class)),
		// --------------------------------------------------------------------

	AuthorizationService::class => fn() => new AuthorizationService(),

	Twig::class => function (ContainerInterface $c) {

		$settings = $c->get('settings')['twig'];
		$view = Twig::create($settings['template_dir'], [
			'cache' => $settings['cache_enabled'] ? __DIR__ . '/../../var/cache/twig' : false,
		]);

		$csrf = $c->get(Slim\Csrf\Guard::class);

		$version = trim(file_get_contents(dirname((dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'VERSION'));

		$viewEnv = $view->getEnvironment();

		$viewEnv->addGlobal('version', $version);

		$viewEnv->addGlobal('csrf', [
			'name_key' => $csrf->getTokenNameKey(),
			'name' => $csrf->getTokenName(),
			'value_key' => $csrf->getTokenValueKey(),
			'value' => $csrf->getTokenValue(),
		]);
		return $view;
	},
];
