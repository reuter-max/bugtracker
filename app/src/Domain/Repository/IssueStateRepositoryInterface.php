<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\IssueState;

interface IssueStateRepositoryInterface {
	public function find(string $id): ?IssueState;

	public function findByKey(string $key): ?IssueState;

	/** @return IssueState[] */
	public function findAll(): array;

	public function save(IssueState $state): void;

	public function delete(IssueState $state): void;
}