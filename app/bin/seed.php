<?php

declare(strict_types=1);

use App\Domain\Entity\Action;
use App\Domain\Entity\Role;
use App\Domain\Entity\User;
use App\Domain\Repository\ActionRepositoryInterface;
use App\Domain\Repository\RoleRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/bootstrap/dependencies.php');
$container = $containerBuilder->build();

/** @var ActionRepositoryInterface $actionRepo */
$actionRepo = $container->get(ActionRepositoryInterface::class);
/** @var RoleRepositoryInterface $roleRepo */
$roleRepo = $container->get(RoleRepositoryInterface::class);
/** @var UserRepositoryInterface $userRepo */
$userRepo = $container->get(UserRepositoryInterface::class);

// -- Actions anlegen (frei erweiterbarer Katalog aller Systemaktionen) --
$actionKeys = [
	'issue.create' => 'Issue anlegen',
	'issue.edit' => 'Issue bearbeiten',
	'issue.delete' => 'Issue loeschen',
	'config.edit' => 'Konfiguration aendern',
	'user.manage' => 'Benutzer verwalten',
];

$actions = [];
foreach ($actionKeys as $key => $description) {
	$action = $actionRepo->findByKey($key) ?? new Action($key, $description);
	$actionRepo->save($action);
	$actions[$key] = $action;
}

// -- Rollen anlegen und Actions zuordnen --
$admin = $roleRepo->findByName('Admin') ?? new Role('Admin');
foreach ($actions as $action) {
	$admin->grant($action);
}
$roleRepo->save($admin);

$adminName = $_ENV['APP_ADMIN_USER'] ?? 'Administrator';
$adminEmail = $_ENV['APP_ADMIN_MAIL'] ?? 'admin@example.com';
$adminPassword = $_ENV['APP_ADMIN_PASS'] ?? 'changeme';

// -- Admin-User anlegen --
if (!$userRepo->findByEmail($adminEmail)) {
	$adminUser = new User($adminEmail, $adminName, password_hash($adminPassword, PASSWORD_DEFAULT));
	$adminUser->addRole($admin);
	$userRepo->save($adminUser);
	echo "Admin-User angelegt: $adminEmail / $adminPassword\n";
} else {
	echo "Admin-User existiert bereits.\n";
}

echo "Seed abgeschlossen.\n";
