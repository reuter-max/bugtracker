<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use App\Application\Service\NotificationService;
use App\Domain\Entity\Issue;
use App\Domain\Entity\User;
use App\Domain\Repository\{IssueRepositoryInterface, IssueStateRepositoryInterface, UserRepositoryInterface};
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

final class CreateIssueAction {
	public function __construct(
		private readonly IssueRepositoryInterface $issues,
		private readonly Twig $view,
		private readonly IssueStateRepositoryInterface $statuses,
		private readonly UserRepositoryInterface $users,
		private readonly Translator $translator,
		private readonly NotificationService $notifications
	) {
	}

	public function __invoke(Request $request, Response $response): Response {
		/** @var User $user */
		$user = $request->getAttribute('user');
		$data = (array) $request->getParsedBody();

		$title = trim((string) ($data['title'] ?? ''));
		$description = trim((string) ($data['description'] ?? ''));

		$defaultStatus = $this->statuses->findByKey(Issue::DEFAULT_STATE_KEY);
		if (!$defaultStatus) {
			$response->getBody()->write('Kein Standard-Status konfiguriert.');
			return $response->withStatus(500);
		}

		if ($title === '') {
			$response = $response->withStatus(422);
			$response->getBody()->write($this->translator->trans('issue.title_empty'));
			return $response;
		}

		$issue = new Issue($title, $description, $user, $defaultStatus);
		$this->issues->save($issue);

		foreach ($this->users->findAll() as $recipient) {
			if ($recipient->getId() !== $user->getId()) {
				$this->notifications->notify(
					$recipient,
					$this->translator->trans(
						'issue.created', [
							'%number%' => $issue->getNumber()
						]
					)
				);
			}
		}

		// HTMX: liefert nur die neue Tabellenzeile zurueck, die per
		// hx-swap="afterbegin" in die bestehende Tabelle eingehaengt wird.
		return $this->view->render($response, 'issues/issue_row.twig', [
			'issue' => $issue,
			'user' => $user,
			'users' => $this->users->findAll(),
			'statuses' => $this->statuses->findAll()
		])->withHeader('HX-Trigger', 'closeModal');
	}
}
