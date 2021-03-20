<?php namespace Camagru;

use Camagru\Auth;
use Camagru\Http\Header;
use Camagru\Http\Response;
use Env;
use Exception\AuthException;

class Application
{
	private $router;
	private $root;

	public function __construct(Router $router, string $root)
	{
		$this->router = $router;
		$this->root = $root;
	}

	public function load(): self
	{
		$iniFile = $this->root . '/config.ini';
		if (!\file_exists($iniFile)) {
			throw new \Exception('Missing config.ini file.');
		}
		$config = \parse_ini_file($this->root . '/config.ini', true);
		if ($config === false) {
			throw new \Exception('Error while reading config.ini file.');
		}
		foreach ($config as $key => $value) {
			Env::setNamespace($key, $value);
		}
		Env::set('Camagru', 'root', $this->root);
		return $this;
	}

	public function run(): self
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
			return $this;
		}

		// Try to match a route and render a response
		$match = $this->router->match($request);
		if ($match !== false) {
			// Check if the route need authentication
			$auth = null;
			if (isset($match['auth']) && $match['auth'] !== null) {
				$auth = new Auth();
				if (($match['auth'] && !$auth->isLoggedIn()) || (!$match['auth'] && $auth->isLoggedIn())) {
					$reason = $match['auth'] ?
					'You need to be logged in to access this page.' :
					'You need to be logged out to access this page.';
					throw new AuthException($reason);
				}
			}

			// Call the route
			$controller = $match['controller'];
			$controller = new $controller($request, $auth);
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
		return $this;
	}
}
