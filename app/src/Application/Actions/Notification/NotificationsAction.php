<?php

namespace App\Application\Actions\Notification;

use App\Domain\Repository\NotificationRepositoryInterface;
use Slim\Psr7\{Request, Response};
use Slim\Views\Twig;

final class NotificationsAction {
	public function __construct(
		private readonly NotificationRepositoryInterface $notifications,
		private readonly Twig $view,
	) {
	}

	public function __invoke(Request $request, Response $response): Response {
		$user = $request->getAttribute('user');
		return $this->view->render($response, 'notifications.twig', [
			'notifications' => $this->notifications->findForUser($user),
			'unread_count' => $this->notifications->countUnread($user),
		]);
	}
}