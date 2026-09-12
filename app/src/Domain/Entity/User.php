<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User {
	#[ORM\Id]
	#[ORM\Column(type: 'string', length: 36, unique: true)]
	private string $id;

	#[ORM\Column(type: 'string', length: 190, unique: true)]
	private string $email;

	#[ORM\Column(type: 'string', length: 190)]
	private string $display_name;

	#[ORM\Column(type: 'string', length: 255)]
	private string $password_hash;

	/** @var Collection<int, Role> */
	#[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
	#[ORM\JoinTable(name: 'user_role')]
	private Collection $roles;

	#[ORM\Column(type: 'boolean')]
	private bool $active = true;

	public function __construct(string $email, string $displayName, string $passwordHash) {
		$this->id = Uuid::uuid4()->toString();
		$this->email = $email;
		$this->display_name = $displayName;
		$this->password_hash = $passwordHash;
		$this->roles = new ArrayCollection();
	}

	public function getId(): string {
		return $this->id;
	}

	public function getEmail(): string {
		return $this->email;
	}

	public function getDisplayName(): string {
		return $this->display_name;
	}

	public function getPasswordHash(): string {
		return $this->password_hash;
	}

	public function isActive(): bool {
		return $this->active;
	}

	public function addRole(Role $role): void {
		if (!$this->roles->contains($role)) {
			$this->roles->add($role);
		}
	}

	public function removeRole(Role $role): void {
		$this->roles->removeElement($role);
	}

	/** @return Collection<int, Role> */
	public function getRoles(): Collection {
		return $this->roles;
	}
}
