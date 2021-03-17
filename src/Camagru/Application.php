<?php namespace Camagru;

use Camagru\Http\Header;
use Camagru\Http\Response;

class Application
{
	private $router;

	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	public function run(): void
	{
		$request = new Http\Request;

		// Send allowed Headers if it's an OPTIONS request
		if ($request->isCors() && $request->getMethod() === 'OPTIONS') {
			$allowedOrigin = $request->resolveAllowedOrigin();
			$response = new Response;
			$response->setHeaders([
				'Access-Control-Allow-Origin' => $allowedOrigin,
				'Access-Control-Allow-Methods' => 'OPTIONS, GET, POST, DELETE',
				'Access-Control-Allow-Headers' => 'Content-Type',
				'Access-Control-Allow-Credentials' => 'false',
			])
				->forRequest($request)
				->render();
			return;
		}

		// Try to match a route and render a response
		$match = $this->router->match($request);
		if ($match !== false) {
			$controller = '\\Controller\\' . $match['controller'];
			$controller = new $controller($request);
			$response = \call_user_func([$controller, $match['function']], ...$match['foundParams']);
			$response->render();
		} else {
			$response = new Response(
				['error' => 'Not found'],
				[Header::CONTENT_TYPE => Header::JSON_TYPE_UTF8],
				Response::NOT_FOUND,
				$request,
			);
			$response->render();
		}
	}
}
