<?php

declare(strict_types=1);

use App\Application\Actions\Auth\LoginAction;
use App\Application\Actions\Issue\{
	AssignIssueAction,
	CreateIssueAction,
	DeleteIssueAction,
	ListIssuesAction,
	NewIssueFormAction,
	UpdateIssueAction
};
use App\Application\Actions\Notification\NotificationsAction;
use App\Application\Middleware\{
	AuthenticationMiddleware,
	PermissionMiddleware
};
use App\Application\Service\AuthorizationService;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;

return function (App $app) {
	$container = $app->getContainer();

	// --- Auth (oeffentlich) --------------------------------------------
	$app->get('/', [LoginAction::class, 'showForm']);
	$app->get('/login', [LoginAction::class, 'showForm']);
	$app->post('/login', [LoginAction::class, 'login']);
	$app->post('/logout', [LoginAction::class, 'logout']);

	// --- Geschuetzter Bereich -------------------------------------------

	// Kleiner Helfer, um PermissionMiddleware pro Route mit dem
	// jeweils benoetigten Action-Key zu instanziieren.
	$requires = function (string $actionKey) use ($container): PermissionMiddleware {
		return new PermissionMiddleware(
			$container->get(AuthorizationService::class),
			$container->get(ResponseFactoryInterface::class),
			$actionKey
		);
	};

	$app->group('', function ($group) use ($requires) {
		$group->get('/issues', ListIssuesAction::class);
		$group->post('/issues', CreateIssueAction::class)->add($requires('issue.create'));
		$group->put('/issues/{id}', UpdateIssueAction::class)->add($requires('issue.edit'));
		$group->put('/issues/{id}/assign', AssignIssueAction::class)->add($requires('issue.edit'));
		$group->delete('/issues/{id}', DeleteIssueAction::class)->add($requires('issue.delete'));
		$group->get('/issues/new', NewIssueFormAction::class);
		$group->get('/notifications', NotificationsAction::class);
	})->add(AuthenticationMiddleware::class);
};
