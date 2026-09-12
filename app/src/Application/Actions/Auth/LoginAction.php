<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Domain\Repository\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

final class LoginAction {
	public function __construct(
		private readonly UserRepositoryInterface $users,
		private readonly Twig $view,
	) {
	}

	public function showForm(Request $request, Response $response): Response {
		return $this->view->render($response, 'login/login.twig');
	}

	public function login(Request $request, Response $response): Response {
		$data = (array) $request->getParsedBody();
		$email = trim((string) ($data['email'] ?? ''));
		$password = (string) ($data['password'] ?? '');

		$user = $this->users->findByEmail($email);

		if (!$user || !password_verify($password, $user->getPasswordHash())) {
			return $this->view->render(
				$response->withStatus(401),
				'login/login_form.twig',
				[
					'error' => 'E-Mail oder Passwort ist falsch.'
				]
			);
		}

		$_SESSION['user_id'] = $user->getId();

		return $response->withHeader('HX-Redirect', '/issues')->withStatus(200);
	}

	public function logout(Request $request, Response $response): Response {
		unset($_SESSION['user_id']);
		session_destroy();

		return $response->withHeader('HX-Redirect', '/login')->withStatus(200);
	}
}
