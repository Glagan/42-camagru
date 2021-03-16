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
