<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Notification;
use App\Domain\Entity\User;

interface NotificationRepositoryInterface {

	/** @return Notification[] */
	public function findForUser(User $user, bool $unreadOnly = false): array;

	public function countUnread(User $user): int;

	public function find(string $id): ?Notification;

	public function save(Notification $notification): void;
}