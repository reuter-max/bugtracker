<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Action;
use App\Domain\Repository\ActionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineActionRepository implements ActionRepositoryInterface {
	public function __construct(private readonly EntityManagerInterface $em) {
	}

	public function find(string $id): ?Action {
		return $this->em->find(Action::class, $id);
	}

	public function findByKey(string $key): ?Action {
		return $this->em->getRepository(Action::class)->findOneBy(['key' => $key]);
	}

	public function findAll(): array {
		return $this->em->getRepository(Action::class)->findAll();
	}

	public function save(Action $action): void {
		$this->em->persist($action);
		$this->em->flush();
	}

	public function delete(Action $action): void {
		$this->em->remove($action);
		$this->em->flush();
	}
}
