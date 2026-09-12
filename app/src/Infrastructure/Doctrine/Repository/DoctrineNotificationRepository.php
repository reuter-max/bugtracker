<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Notification;
use App\Domain\Entity\User;
use App\Domain\Repository\NotificationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNotificationRepository implements NotificationRepositoryInterface {
	public function __construct(
		private readonly EntityManagerInterface $em
	) {
	}

	public function findForUser(User $user, bool $unreadOnly = false): array {
		$criteria = ['recipient' => $user];
		if ($unreadOnly) {
			$criteria['read'] = false;
		}
		return $this->em->getRepository(Notification::class)->findBy($criteria, ['created_at' => 'DESC'], 50);
	}

	public function countUnread(User $user): int {
		return (int) $this->em->createQueryBuilder()
			->select('COUNT(n.id)')
			->from(Notification::class, 'n')
			->where('n.recipient = :user')
			->andWhere('n.read = false')
			->setParameter('user', $user)
			->getQuery()
			->getSingleScalarResult();
	}

	public function find(string $id): ?Notification {
		return $this->em->find(Notification::class, $id);
	}

	public function save(Notification $notification): void {
		$this->em->persist($notification);
		$this->em->flush();
	}
}