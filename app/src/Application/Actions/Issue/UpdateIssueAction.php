<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use App\Domain\Repository\IssueRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

final class UpdateIssueAction {

	private const ALLOWED_STATES = [
		'open',
		'in_progress',
		'closed'
	];

	public function __construct(
		private readonly IssueRepositoryInterface $issues,
		private readonly Twig $view,
		private readonly \App\Domain\Repository\UserRepositoryInterface $users
	) {
	}

	public function __invoke(Request $request, Response $response, array $args): Response {
		$issue = $this->issues->find($args['id']);
		if (!$issue) {
			$response->getBody()->write('Issue nicht gefunden.');
			return $response->withStatus(404);
		}

		$data = (array) $request->getParsedBody();

		if (isset($data['status'])) {
			if (!in_array($data['status'], self::ALLOWED_STATES, true)) {
				$response->getBody()->write('Ungueltiger Status.');
				return $response->withStatus(422);
			}
			$issue->setStatus($data['status']);
		}

		$this->issues->save($issue);

		return $this->view->render($response, 'issues/_issue_row.twig', [
			'users' => $this->users->findAll(),
			'issue' => $issue
		]);
	}
}