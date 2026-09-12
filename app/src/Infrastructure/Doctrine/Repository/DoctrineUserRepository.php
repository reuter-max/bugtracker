<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserRepositoryInterface {
	public function __construct(private readonly EntityManagerInterface $em) {
	}

	public function find(string $id): ?User {
		return $this->em->find(User::class, $id);
	}

	public function findByEmail(string $email): ?User {
		return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
	}

	public function findAll(): array {
		return $this->em->getRepository(User::class)->findAll();
	}

	public function save(User $user): void {
		$this->em->persist($user);
		$this->em->flush();
	}

	public function delete(User $user): void {
		$this->em->remove($user);
		$this->em->flush();
	}
}
