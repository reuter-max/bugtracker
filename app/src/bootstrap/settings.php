<?php

declare(strict_types=1);

return [
	'db' => [
		'driver' => 'pdo_pgsql',
		'host' => $_ENV['DB_HOST'] ?? 'db',
		'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
		'dbname' => $_ENV['DB_NAME'] ?? 'bugtracker_db',
		'user' => $_ENV['DB_USER'] ?? 'bugtracker_usr',
		'password' => $_ENV['DB_PASS'] ?? 'bugtracker_pass',
	],
	'doctrine' => [
		// Attribute-Mapping direkt aus den Domain-Entities.
		// is_dev_mode = true fuehrt zu Klartext-Fehlern & kein Caching --
		// fuer Produktion spaeter auf false stellen + echten Cache-Adapter.
		'entity_dir' => __DIR__ . '/../Domain/Entity',
		'is_dev_mode' => true,
	],
	'twig' => [
		'template_dir' => __DIR__ . '/../../templates',
		'cache_enabled' => false,
	],
];
