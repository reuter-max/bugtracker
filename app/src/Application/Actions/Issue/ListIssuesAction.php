<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use App\Domain\Repository\IssueRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

final class ListIssuesAction {
	public function __construct(
		private readonly IssueRepositoryInterface $issues,
		private readonly Twig $view,
		private readonly \App\Domain\Repository\UserRepositoryInterface $users
	) {
	}

	public function __invoke(Request $request, Response $response): Response {
		$status = $request->getQueryParams()['status'] ?? null;
		$issues = $status ? $this->issues->findByStatus($status) : $this->issues->findAll();

		$isHtmxRequest = $request->getHeaderLine('HX-Request') === 'true';
		$template = $isHtmxRequest ? 'issues/_issue_table.twig' : 'issues/list.twig';

		return $this->view->render($response, $template, [
			'issues' => $issues,
			'users' => $this->users->findAll(),
			'user' => $request->getAttribute('user'),
		]);
	}
}
