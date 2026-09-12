<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Domain\Repository\UserRepositoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Simple sessionbasierte Authentifizierung. Laedt den eingeloggten User
 * (falls vorhanden) und legt ihn als Request-Attribut "user" ab, damit
 * Actions und die naechste Middleware (PermissionMiddleware) ihn nutzen
 * koennen.
 */
final class AuthenticationMiddleware implements MiddlewareInterface {
	public function __construct(
		private readonly UserRepositoryInterface $users,
		private readonly ResponseFactoryInterface $responseFactory,
	) {
	}

	public function process(Request $request, RequestHandler $handler): Response {
		$userId = $_SESSION['user_id'] ?? null;
		$user = $userId ? $this->users->find($userId) : null;

		if (!$user || !$user->isActive()) {
			// Kein eingeloggter User -> zur Login-Seite umleiten.
			// HTMX-Requests bekommen stattdessen einen HX-Redirect-Header,
			// damit ein clientseitiger Redirect ausgeloest wird.
			$response = $this->responseFactory->createResponse(303);
			if ($request->getHeaderLine('HX-Request') === 'true') {
				return $response->withHeader('HX-Redirect', '/login');
			}
			return $response->withHeader('Location', '/login');
		}

		$request = $request->withAttribute('user', $user);

		return $handler->handle($request);
	}
}
