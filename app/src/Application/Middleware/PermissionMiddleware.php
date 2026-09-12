<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Service\AuthorizationService;
use App\Domain\Entity\User;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Wird pro Route mit einem konkreten Action-Key konfiguriert, z.B.:
 *
 *   $app->post('/issues', CreateIssueAction::class)
 *	   ->add(new PermissionMiddleware($authz, $responseFactory, 'issue.create'));
 *
 * Dadurch ist im Routing sofort ersichtlich, welche Aktion welche
 * Permission braucht -- ohne dass die Action-Klasse selbst etwas ueber
 * Rollen wissen muss.
 */
final class PermissionMiddleware implements MiddlewareInterface {
	public function __construct(
		private readonly AuthorizationService $authz,
		private readonly ResponseFactoryInterface $responseFactory,
		private readonly string $requiredAction,
	) {
	}

	public function process(Request $request, RequestHandler $handler): Response {
		/** @var User $user */
		$user = $request->getAttribute('user');

		if (!$this->authz->can($user, $this->requiredAction)) {
			$response = $this->responseFactory->createResponse(403);
			$response->getBody()->write('Keine Berechtigung fuer diese Aktion.');
			return $response;
		}

		return $handler->handle($request);
	}
}
