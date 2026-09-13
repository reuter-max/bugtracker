<?php

declare(strict_types=1);

namespace App\Application\Actions\Locale;

use App\Application\Service\AvailableLocalesProvider;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class LocaleSwitchAction {

	public function __construct(
		private readonly AvailableLocalesProvider $locales
	) {
	}

	public function __invoke(Request $request, Response $response, array $args): Response {
		$data = (array) $request->getParsedBody();
		$locale = $data['locale'] ?? $_SESSION['locale'] ?? 'en';

		if (!in_array($locale, $this->locales->all(), true)) {
			$response->getBody()->write("Unknown Language ($locale).");
			return $response->withStatus(422);
		}

		$_SESSION['locale'] = $locale;

		// HX-Refresh laesst htmx einen normalen, vollen Seiten-Reload ausloesen --
		// dadurch greift die LocaleMiddleware beim naechsten Request sofort mit
		// dem neuen Wert, ohne dass wir hier Templates manuell neu rendern muessen.
		return $response->withHeader('HX-Refresh', 'true')->withStatus(200);
	}
}