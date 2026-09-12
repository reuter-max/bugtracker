<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Role;

interface RoleRepositoryInterface {
	public function find(string $id): ?Role;

	public function findByName(string $name): ?Role;

	/** @return Role[] */
	public function findAll(): array;

	public function save(Role $role): void;

	public function delete(Role $role): void;
}
