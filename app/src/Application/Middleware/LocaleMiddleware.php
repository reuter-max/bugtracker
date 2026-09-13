<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Symfony\Component\Translation\Translator;

final class LocaleMiddleware implements MiddlewareInterface {
	private const FALLBACK_LOCALE = 'en';

	public function __construct(private readonly Translator $translator) {
	}

	public function process(Request $request, RequestHandler $handler): Response {
		$locale = $_SESSION['locale'] ?? $_ENV['APP_LOCALE'] ?? self::FALLBACK_LOCALE;
		$this->translator->setLocale($locale);

		return $handler->handle($request);
	}
}