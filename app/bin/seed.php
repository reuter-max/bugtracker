#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Domain\Entity\Action;
use App\Domain\Entity\Role;
use App\Domain\Entity\User;
use App\Domain\Repository\ActionRepositoryInterface;
use App\Domain\Repository\RoleRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
	Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();
}

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

$developer = $roleRepo->findByName('Entwickler') ?? new Role('Entwickler');
$developer->grant($actions['issue.create']);
$developer->grant($actions['issue.edit']);
$roleRepo->save($developer);

$visitor = $roleRepo->findByName('Besucher') ?? new Role('Besucher');
// Besucher bekommt bewusst keine Actions -- darf nur lesen.
$roleRepo->save($visitor);

// -- Admin-User anlegen --
if (!$userRepo->findByEmail('admin@example.com')) {
	$adminUser = new User('admin@example.com', 'Administrator', password_hash('changeme', PASSWORD_DEFAULT));
	$adminUser->addRole($admin);
	$userRepo->save($adminUser);
	echo "Admin-User angelegt: admin@example.com / changeme\n";
} else {
	echo "Admin-User existiert bereits.\n";
}

echo "Seed abgeschlossen.\n";
