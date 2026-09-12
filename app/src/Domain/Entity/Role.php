<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'roles')]
class Role {
	#[ORM\Id]
	#[ORM\Column(type: 'string', length: 36, unique: true)]
	private string $id;

	#[ORM\Column(type: 'string', length: 100, unique: true)]
	private string $name;

	/** @var Collection<int, Action> */
	#[ORM\ManyToMany(targetEntity: Action::class, inversedBy: 'roles')]
	#[ORM\JoinTable(name: 'role_action')]
	private Collection $actions;

	/** @var Collection<int, User> */
	#[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'roles')]
	private Collection $users;

	public function __construct(string $name) {
		$this->id = Uuid::uuid4()->toString();
		$this->name = $name;
		$this->actions = new ArrayCollection();
		$this->users = new ArrayCollection();
	}

	public function getId(): string {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function grant(Action $action): void {
		if (!$this->actions->contains($action)) {
			$this->actions->add($action);
		}
	}

	public function revoke(Action $action): void {
		$this->actions->removeElement($action);
	}

	public function hasAction(string $actionKey): bool {
		foreach ($this->actions as $action) {
			if ($action->getKey() === $actionKey) {
				return true;
			}
		}
		return false;
	}

	/** @return Collection<int, Action> */
	public function getActions(): Collection {
		return $this->actions;
	}
}
