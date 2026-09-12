<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use App\Domain\Entity\Issue;
use App\Domain\Entity\User;
use App\Domain\Repository\IssueRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

final class CreateIssueAction {
	public function __construct(
		private readonly IssueRepositoryInterface $issues,
		private readonly Twig $view,
		private readonly \App\Domain\Repository\UserRepositoryInterface $users
	) {
	}

	public function __invoke(Request $request, Response $response): Response {
		/** @var User $user */
		$user = $request->getAttribute('user');
		$data = (array) $request->getParsedBody();

		$title = trim((string) ($data['title'] ?? ''));
		$description = trim((string) ($data['description'] ?? ''));

		if ($title === '') {
			$response = $response->withStatus(422);
			$response->getBody()->write('Titel darf nicht leer sein.');
			return $response;
		}

		$issue = new Issue($title, $description, $user);
		$this->issues->save($issue);

		// HTMX: liefert nur die neue Tabellenzeile zurueck, die per
		// hx-swap="afterbegin" in die bestehende Tabelle eingehaengt wird.
		return $this->view->render($response, 'issues/_issue_row.twig', [
			'issue' => $issue,
			'user' => $user,
			'users' => $this->users->findAll(),
		])->withHeader('HX-Trigger', 'closeModal');
	}
}
