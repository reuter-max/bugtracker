<?php

declare(strict_types=1);

namespace App\Application\Actions\Issue;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

final class NewIssueFormAction {
	public function __construct(private readonly Twig $view) {
	}

	public function __invoke(Request $request, Response $response): Response {
		return $this->view->render($response, 'issues/issue_form.twig');
	}
}