<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\User;

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
