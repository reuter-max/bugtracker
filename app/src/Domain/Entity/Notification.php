<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'notifications')]
class Notification {
	#[ORM\Id]
	#[ORM\Column(type: 'string', length: 36, unique: true)]
	private string $id;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'recipient_id', referencedColumnName: 'id', nullable: false)]
	private User $recipient;

	#[ORM\Column(type: 'string', length: 500)]
	private string $message;

	#[ORM\Column(type: 'string', length: 30)]
	private string $type;

	#[ORM\Column(type: 'boolean')]
	private bool $read = false;

	#[ORM\Column(type: 'datetime_immutable')]
	private DateTimeImmutable $created_at;

	public function __construct(User $recipient, string $message, string $type = 'info') {
		$this->id = Uuid::uuid4()->toString();
		$this->recipient = $recipient;
		$this->message = $message;
		$this->type = $type;
		$this->created_at = new DateTimeImmutable();
	}

	public function getId(): string {
		return $this->id;
	}

	public function getRecipient(): User {
		return $this->recipient;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function getType(): string {
		return $this->type;
	}

	public function isRead(): bool {
		return $this->read;
	}

	public function markAsRead(): void {
		$this->read = true;
	}

	public function getCreatedAt(): DateTimeImmutable {
		return $this->created_at;
	}
}