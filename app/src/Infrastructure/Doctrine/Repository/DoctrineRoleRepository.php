<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Role;
use App\Domain\Repository\RoleRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineRoleRepository implements RoleRepositoryInterface {
	public function __construct(private readonly EntityManagerInterface $em) {
	}

	public function find(string $id): ?Role {
		return $this->em->find(Role::class, $id);
	}

	public function findByName(string $name): ?Role {
		return $this->em->getRepository(Role::class)->findOneBy(['name' => $name]);
	}

	public function findAll(): array {
		return $this->em->getRepository(Role::class)->findAll();
	}

	public function save(Role $role): void {
		$this->em->persist($role);
		$this->em->flush();
	}

	public function delete(Role $role): void {
		$this->em->remove($role);
		$this->em->flush();
	}
}
