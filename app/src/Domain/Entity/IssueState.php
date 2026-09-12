<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'issue_states')]
class IssueState {
	#[ORM\Id]
	#[ORM\Column(type: 'string', length: 36, unique: true)]
	private string $id;

	#[ORM\Column(type: 'string', length: 50, unique: true)]
	private string $key;

	#[ORM\Column(type: 'integer')]
	private int $sort_order;

	public function __construct(string $key, int $sort_order) {
		$this->id = Uuid::uuid4()->toString();
		$this->key = $key;
		$this->sort_order = $sort_order;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getKey(): string {
		return $this->key;
	}

	public function getsort_order(): int {
		return $this->sort_order;
	}
}