<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use App\Domain\Repository\IssueRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

final class AssignIssueAction {
	public function __construct(
		private readonly IssueRepositoryInterface $issues,
		private readonly UserRepositoryInterface $users,
		private readonly Twig $view,
	) {
	}

	public function __invoke(Request $request, Response $response, array $args): Response {
		$issue = $this->issues->find($args['id']);
		if (!$issue) {
			$response->getBody()->write('Issue nicht gefunden.');
			return $response->withStatus(404);
		}

		$data = (array) $request->getParsedBody();
		$assigneeId = $data['assignee_id'] ?? '';

		$assignee = $assigneeId !== '' ? $this->users->find($assigneeId) : null;
		if ($assigneeId !== '' && !$assignee) {
			$response->getBody()->write('Unbekannter Benutzer.');
			return $response->withStatus(422);
		}

		$issue->assignTo($assignee);
		$this->issues->save($issue);

		return $this->view->render($response, 'issues/_issue_row.twig', [
			'issue' => $issue,
			'users' => $this->users->findAll(),
		]);
	}
}