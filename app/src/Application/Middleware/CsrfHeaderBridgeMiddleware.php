<?php

namespace App\Application\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Slim\Csrf\Guard;
use \Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class CsrfHeaderBridgeMiddleware implements MiddlewareInterface {

	private const HEADER_NAME = 'X-CSRF-Name';
	private const HEADER_VALUE = 'X-CSRF-Value';

	public function __construct(private readonly Guard $csrf) {
	}

	public function process(Request $request, RequestHandler $handler): Response {
		if ($request->hasHeader(self::HEADER_NAME) && $request->hasHeader(self::HEADER_VALUE)) {
			$body = (array) $request->getParsedBody();
			$body[$this->csrf->getTokenNameKey()] = $request->getHeaderLine(self::HEADER_NAME);
			$body[$this->csrf->getTokenValueKey()] = $request->getHeaderLine(self::HEADER_VALUE);
			$request = $request->withParsedBody($body);
		}

		return $handler->handle($request);
	}
}