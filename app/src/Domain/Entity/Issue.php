<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'issues')]
class Issue {
	public const STATUS_OPEN = 'open';
	public const STATUS_IN_PROGRESS = 'in_progress';
	public const STATUS_CLOSED = 'closed';

	#[ORM\Id]
	#[ORM\Column(type: 'string', length: 36, unique: true)]
	private string $id;

	#[ORM\Column(type: 'integer', insertable: false, updatable: false)]
	private int $number;

	#[ORM\Column(type: 'string', length: 200)]
	private string $title;

	#[ORM\Column(type: 'text')]
	private string $description;

	#[ORM\Column(type: 'string', length: 30)]
	private string $status;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'reporter_id', referencedColumnName: 'id', nullable: false)]
	private User $reporter;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'assignee_id', referencedColumnName: 'id', nullable: true)]
	private ?User $assignee = null;

	#[ORM\Column(type: 'datetime_immutable')]
	private DateTimeImmutable $created_at;

	#[ORM\Column(type: 'datetime_immutable')]
	private DateTimeImmutable $updated_at;

	public function __construct(string $title, string $description, User $reporter) {
		$this->id = Uuid::uuid4()->toString();
		$this->title = $title;
		$this->description = $description;
		$this->status = self::STATUS_OPEN;
		$this->reporter = $reporter;
		$this->created_at = new DateTimeImmutable();
		$this->updated_at = new DateTimeImmutable();
	}

	public function getId(): string {
		return $this->id;
	}

	public function getNumber(): int {
		return $this->number;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getStatus(): string {
		return $this->status;
	}

	public function setStatus(string $status): void {
		$this->status = $status;
		$this->touch();
	}

	public function getReporter(): User {
		return $this->reporter;
	}

	public function getAssignee(): ?User {
		return $this->assignee;
	}

	public function assignTo(?User $user): void {
		$this->assignee = $user;
		$this->touch();
	}

	public function getCreatedAt(): DateTimeImmutable {
		return $this->created_at;
	}

	public function getUpdatedAt(): DateTimeImmutable {
		return $this->updated_at;
	}

	private function touch(): void {
		$this->updated_at = new DateTimeImmutable();
	}
}
