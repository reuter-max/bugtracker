<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Eine "Action" ist ein eindeutiger Schluessel fuer eine erlaubbare
 * Systemaktion, z.B. "issue.create", "issue.edit", "config.edit".
 * Neue Aktionen koennen jederzeit ergaenzt werden, ohne Code zu aendern --
 * die Pruefung im Application-Layer fragt nur nach dem Schluessel.
 */
#[ORM\Entity]
#[ORM\Table(name: 'actions')]
class Action {
	#[ORM\Id]
	#[ORM\Column(type: 'string', length: 36, unique: true)]
	private string $id;

	#[ORM\Column(type: 'string', length: 150, unique: true)]
	private string $key;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $description;

	/** @var Collection<int, Role> */
	#[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'actions')]
	private Collection $roles;

	public function __construct(string $key, ?string $description = null) {
		$this->id = Uuid::uuid4()->toString();
		$this->key = $key;
		$this->description = $description;
		$this->roles = new ArrayCollection();
	}

	public function getId(): string {
		return $this->id;
	}

	public function getKey(): string {
		return $this->key;
	}

	public function getDescription(): ?string {
		return $this->description;
	}
}
