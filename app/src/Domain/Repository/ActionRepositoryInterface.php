<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Action;

interface ActionRepositoryInterface {
	public function find(string $id): ?Action;

	public function findByKey(string $key): ?Action;

	/** @return Action[] */
	public function findAll(): array;

	public function save(Action $action): void;

	public function delete(Action $action): void;
}
