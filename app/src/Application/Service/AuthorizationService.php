<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\User;

/**
 * Zentrale, generische Rechtepruefung. Kennt keine konkreten Rollennamen
 * (kein "if role === admin") -- fragt stattdessen nur, ob irgendeine der
 * Rollen des Users die angefragte Action erlaubt. Neue Rollen/Aktionen
 * lassen sich rein per Datenbank-Eintrag ergaenzen, ohne hier Code
 * anzufassen.
 */
final class AuthorizationService {
	public function can(User $user, string $actionKey): bool {
		foreach ($user->getRoles() as $role) {
			if ($role->hasAction($actionKey)) {
				return true;
			}
		}

		return false;
	}
}
