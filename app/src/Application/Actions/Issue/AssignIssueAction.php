<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use App\Application\Service\NotificationService;
use App\Domain\Repository\{IssueRepositoryInterface, IssueStateRepositoryInterface};
use App\Domain\Repository\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

final class AssignIssueAction {
	public function __construct(
		private readonly IssueRepositoryInterface $issues,
		private readonly UserRepositoryInterface $users,
		private readonly IssueStateRepositoryInterface $statuses,
		private readonly Twig $view,
		private readonly Translator $translator,
		private readonly NotificationService $notifications
	) {
	}

	public function __invoke(Request $request, Response $response, array $args): Response {
		$issue = $this->issues->find($args['id']);
		if (!$issue) {
			$response->getBody()->write($this->translator->trans('issue.not_found'));
			return $response->withStatus(404);
		}

		$previousAssignee = $issue->getAssignee();

		$data = (array) $request->getParsedBody();
		$assigneeId = $data['assignee_id'] ?? '';

		$assignee = $assigneeId !== '' ? $this->users->find($assigneeId) : null;
		if ($assigneeId !== '' && !$assignee) {
			$response->getBody()->write($this->translator->trans('user.not_found'));
			return $response->withStatus(422);
		}

		$issue->assignTo($assignee);
		$this->issues->save($issue);

		if ($assignee) {
			$this->notifications->notify(
				$assignee,
				$this->translator->trans(
					'issue.assigned', [
						'%number%' => $issue->getId()
					]
				)
			);
		}

		if ($previousAssignee && $assignee !== $previousAssignee && $assignee === null) {
			$this->notifications->notify(
				$previousAssignee,
				$this->translator->trans(
					'issue.unassigned', [
						'%number%' => $issue->getId()
					]
				)
			);
		}

		return $this->view->render($response, 'issues/issue_row.twig', [
			'issue' => $issue,
			'users' => $this->users->findAll(),
			'statuses' => $this->statuses->findAll()
		]);
	}
}