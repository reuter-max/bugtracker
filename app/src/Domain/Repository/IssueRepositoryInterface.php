<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Issue;

/**
 * Der komplette Application-Layer (Actions) kennt nur dieses Interface,
 * niemals Doctrine direkt. Ein Wechsel der Persistenz (z.B. auf MongoDB)
 * bedeutet: neue Implementierung dieses Interfaces schreiben und in der
 * DI-Konfiguration (bootstrap/dependencies.php) austauschen -- der Rest
 * der Anwendung bleibt unveraendert.
 */
interface IssueRepositoryInterface {
	public function find(string $id): ?Issue;

	/** @return Issue[] */
	public function findAll(): array;

	/** @return Issue[] */
	public function findByStatus(string $status): array;

	public function save(Issue $issue): void;

	public function delete(Issue $issue): void;
}
