<?php

declare(strict_types=1);

namespace App\Application\Service;

final class VersionService {

	public function __construct(
	) {
	}

	public function getCurrentVersion(): string {
		return trim(file_get_contents(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'VERSION'));
	}

	/**
	 * 
	 * @return ?array{current: string, latest: string, "update_available": bool|int, url: mixed|null}
	 */
	public function check(): ?array {
		$response = @file_get_contents(sprintf(
			'https://api.github.com/repos/%s/releases/latest',
			'reuter-max/bugtracker'
		), false, stream_context_create([
				'http' => [
					'method' => 'GET',
					'header' => implode("\r\n", [
						'Accept: application/vnd.github+json',
						'User-Agent: Bugtracker-Version-Checker',
					]),
					'timeout' => 3,
				],
			]));

		if ($response === false) {
			return null;
		}

		$release = json_decode($response, true);

		if (!isset($release['tag_name'])) {
			return null;
		}

		$latestVersion = ltrim($release['tag_name'], 'v');
		$currentVersion = $this->getCurrentVersion();

		return [
			'url' => $release['html_url'] ?? null,
			'current' => $currentVersion,
			'latest' => $latestVersion,
			'update_available' => version_compare(
				$latestVersion,
				$currentVersion,
				'>'
			),
		];
	}
}