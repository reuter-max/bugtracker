<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\IssueState;
use App\Domain\Repository\IssueStateRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineIssueStateRepository implements IssueStateRepositoryInterface {
	public function __construct(private readonly EntityManagerInterface $em) {
	}

	public function find(string $id): ?IssueState {
		return $this->em->find(IssueState::class, $id);
	}

	public function findByKey(string $key): ?IssueState {
		return $this->em->getRepository(IssueState::class)->findOneBy(['key' => $key]);
	}

	public function findAll(): array {
		return $this->em->getRepository(IssueState::class)->findBy([], ['sort_order' => 'ASC']);
	}

	public function save(IssueState $state): void {
		$this->em->persist($state);
		$this->em->flush();
	}

	public function delete(IssueState $state): void {
		$this->em->remove($state);
		$this->em->flush();
	}
}