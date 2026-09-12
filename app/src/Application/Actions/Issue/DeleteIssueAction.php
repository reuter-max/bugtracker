<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use App\Application\Service\NotificationService;
use App\Domain\Repository\IssueRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Translation\Translator;

final class DeleteIssueAction {
	public function __construct(
		private readonly IssueRepositoryInterface $issues,
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

		$this->issues->delete($issue);

		// Leerer Body mit 200 -> hx-swap="outerHTML" auf der Zeile entfernt sie komplett
		return $response->withStatus(200);
	}
}