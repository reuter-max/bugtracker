<?php

declare(strict_types=1);

use App\Application\Service\{AuthorizationService, AvailableLocalesProvider, VersionChecker, VersionService};
use App\Domain\Entity\IssueState;
use App\Domain\Repository\{ActionRepositoryInterface, IssueStateRepositoryInterface, NotificationRepositoryInterface};
use App\Domain\Repository\IssueRepositoryInterface;
use App\Domain\Repository\RoleRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Doctrine\Repository\{
	DoctrineActionRepository,
	DoctrineIssueStateRepository,
	DoctrineIssueRepository,
	DoctrineNotificationRepository,
	DoctrineRoleRepository,
	DoctrineUserRepository,
};
use Doctrine\ORM\{
	EntityManager,
	EntityManagerInterface,
	ORMSetup
};
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Twig\TwigFunction;
use Predis\Client as RedisClient;

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

	IssueStateRepositoryInterface::class => fn(ContainerInterface $c) =>
		new DoctrineIssueStateRepository($c->get(EntityManagerInterface::class)),

	NotificationRepositoryInterface::class => fn(ContainerInterface $c) =>
		new DoctrineNotificationRepository($c->get(EntityManagerInterface::class)),
		// --------------------------------------------------------------------

	AuthorizationService::class => fn() => new AuthorizationService(),

	VersionService::class => fn() => new VersionService(),

	AvailableLocalesProvider::class => fn() => new AvailableLocalesProvider(
		dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'translations'
	),

	RedisClient::class => fn() => new RedisClient([
		'scheme' => 'tcp',
		'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
		'port' => $_ENV['REDIS_PORT'] ?? 6379,
	]),

	Translator::class => function (ContainerInterface $c) {
		$translator = new Translator($_ENV['APP_LOCALE'] ?? 'en');
		$translator->setFallbackLocales(['de']);
		$translator->addLoader('yaml', new YamlFileLoader());

		$translationsDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'translations';
		foreach ($c->get(AvailableLocalesProvider::class)->all() as $locale) {
			$translator->addResource('yaml', $translationsDir . "/{$locale}.yaml", $locale);
		}

		return $translator;
	},

	Twig::class => function (ContainerInterface $c) {

		$settings = $c->get('settings')['twig'];
		$view = Twig::create($settings['template_dir'], [
			'cache' => $settings['cache_enabled'] ? __DIR__ . '/../../var/cache/twig' : false,
		]);


		$viewEnv = $view->getEnvironment();

		$viewEnv->addGlobal('websocket_port', $_ENV['WEBSOCKET_PORT'] ?? 8081);
		$viewEnv->addGlobal('current_locale', $_SESSION['locale'] ?? $_ENV['APP_LOCALE'] ?? 'en');
		$viewEnv->addGlobal('available_locales', $c->get(AvailableLocalesProvider::class)->all());

		$versionCheck = $c->get(VersionService::class);
		$viewEnv->addGlobal('version', $versionCheck->check());

		$csrf = $c->get(Guard::class);
		$viewEnv->addGlobal('csrf', [
			'name_key' => $csrf->getTokenNameKey(),
			'name' => $csrf->getTokenName(),
			'value_key' => $csrf->getTokenValueKey(),
			'value' => $csrf->getTokenValue(),
		]);

		$viewEnv->addExtension(new TranslationExtension($c->get(Translator::class)));
		$translator = $c->get(Translator::class);

		// Twing-Funktion "locale_name()", um die Sprachenauswahl immer in Ihrer eigenen Sprache darzustellen
		$view->getEnvironment()->addFunction(new TwigFunction('locale_name', function (string $locale) use ($translator) {
			return \Locale::getDisplayLanguage($locale, $locale);
		}));

		// Twing-Funktion "status_label()", um die Labels der Statuswerte localized darzustellen
		$view->getEnvironment()->addFunction(new TwigFunction('status_label', function (IssueState $status) use ($translator) {
			$key = 'status.' . $status->getKey();
			return $translator->getCatalogue()->has($key)
				? $translator->trans($key)
				: throw new \InvalidArgumentException("Status label for key '{$status->getKey()}' not found.");
		}));

		return $view;
	},
];
