<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepositoryInterface {
	public function find(string $id): ?User;

	public function findByEmail(string $email): ?User;

	/** @return User[] */
	public function findAll(): array;

	public function save(User $user): void;

	public function delete(User $user): void;
}
