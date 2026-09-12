<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use App\Application\Service\NotificationService;
use App\Domain\Repository\{IssueRepositoryInterface, IssueStateRepositoryInterface, UserRepositoryInterface};
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

final class UpdateIssueAction {

	public function __construct(
		private readonly IssueRepositoryInterface $issues,
		private readonly Twig $view,
		private readonly UserRepositoryInterface $users,
		private readonly IssueStateRepositoryInterface $statuses,
		private readonly Translator $translator,
		private readonly NotificationService $notifications,
	) {
	}

	public function __invoke(Request $request, Response $response, array $args): Response {
		$issue = $this->issues->find($args['id']);
		if (!$issue) {
			$response->getBody()->write($this->translator->trans('issue.not_found'));
			return $response->withStatus(404);
		}

		$data = (array) $request->getParsedBody();

		$status = $this->statuses->findByKey($data['status']);
		if (!$status) {
			$response->getBody()->write($this->translator->trans('status._invalid'));
			return $response->withStatus(422);
		}
		$issue->setStatus($status);

		$this->notifications->notify(
			$issue->getAssignee(),
			$this->translator->trans(
				'issue.changed', [
					'%number%' => $issue->getId()
				]
			)
		);

		$this->issues->save($issue);

		return $this->view->render($response, 'issues/issue_row.twig', [
			'users' => $this->users->findAll(),
			'statuses' => $this->statuses->findAll(),
			'issue' => $issue
		]);
	}
}