#!/usr/bin/env php
<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command as MigrationsCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/bootstrap/dependencies.php');
$container = $containerBuilder->build();

$em = $container->get(EntityManagerInterface::class);

$config = new PhpFile(__DIR__ . '/../migrations.php');
$dependencyFactory = DependencyFactory::fromEntityManager($config, new ExistingEntityManager($em));

$app = new Application('bugtracker-migrations');
$app->addCommands([
	new MigrationsCommand\MigrateCommand($dependencyFactory),
	new MigrationsCommand\DiffCommand($dependencyFactory),
	new MigrationsCommand\GenerateCommand($dependencyFactory),
	new MigrationsCommand\StatusCommand($dependencyFactory),
	new MigrationsCommand\ExecuteCommand($dependencyFactory),
]);
$app->run();
