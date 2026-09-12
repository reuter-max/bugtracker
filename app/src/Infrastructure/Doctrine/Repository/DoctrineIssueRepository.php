<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Issue;
use App\Domain\Repository\IssueRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineIssueRepository implements IssueRepositoryInterface {
	public function __construct(private readonly EntityManagerInterface $em) {
	}

	public function find(string $id): ?Issue {
		return $this->em->find(Issue::class, $id);
	}

	public function findAll(): array {
		return $this->em->getRepository(Issue::class)->findBy([], ['created_at' => 'DESC']);
	}

	public function findByStatus(string $status): array {
		return $this->em->createQueryBuilder()
			->select('i')
			->from(Issue::class, 'i')
			->join('i.status', 's')
			->where('s.key = :key')
			->setParameter('key', $status)
			->orderBy('i.created_at', 'DESC')
			->getQuery()
			->getResult();
	}

	public function save(Issue $issue): void {
		$isNew = !$this->em->contains($issue);
		$this->em->persist($issue);
		$this->em->flush();
		if ($isNew) {
			$this->em->refresh($issue);
		}
	}

	public function delete(Issue $issue): void {
		$this->em->remove($issue);
		$this->em->flush();
	}
}
