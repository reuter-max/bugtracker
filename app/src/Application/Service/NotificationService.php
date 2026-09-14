<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Notification;
use App\Domain\Entity\User;
use App\Domain\Repository\NotificationRepositoryInterface;
use Predis\Client as RedisClient;

final class NotificationService {
	public function __construct(
		private readonly NotificationRepositoryInterface $notifications,
		private readonly RedisClient $redis,
	) {
	}

	public function notify(User $recipient, string $message, string $type = 'info'): void {
		$notification = new Notification($recipient, $message, $type);
		$this->notifications->save($notification);

		// Fuer sofortige Zustellung, FALLS der Empfaenger gerade per WebSocket
		// verbunden ist. Ist er es nicht, bleibt die Nachricht trotzdem in der
		// DB und erscheint beim naechsten Laden/Aktualisieren der Inbox.
		$this->redis->publish('notifications', json_encode([
			'recipient_id' => $recipient->getId(),
			'id' => $notification->getId(),
			'message' => $message,
			'type' => $type,
		]));
	}
}