<?php

declare(strict_types=1);

namespace App\Application\Service;

final class AvailableLocalesProvider {
	public function __construct(private readonly string $translationsDir) {
	}

	/** @return string[] */
	public function all(): array {
		$locales = [];

		foreach (glob($this->translationsDir . '/*.yaml') as $file) {
			if (preg_match('/([a-z]{2}(?:_[A-Z]{2})?)\.yaml$/', basename($file), $matches)) {
				$locales[] = $matches[1];
			}
		}

		sort($locales);

		return $locales;
	}
}