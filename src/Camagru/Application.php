<?php namespace Camagru;

use Camagru\Auth;
use Camagru\Http\JSONResponse;
use Camagru\Http\Request;
use Camagru\Http\Response;
use Env;
use Exception\AuthException;
use Exception\HTTPException;
use Exception\LoggedException;

class Application
{
	private $router;
	private $root;

	public function __construct(Router $router, string $root)
	{
		$this->router = $router;
		$this->root = $root;
	}

	public function run(Request $request): Response
	{
		try {
			// Send allowed Headers if it's an OPTIONS request
			if ($request->isCors() && $request->getMethod() === 'OPTIONS') {
				$allowedOrigin = $request->resolveAllowedOrigin();
				$response = new Response;
				$response->setHeaders([
					'Access-Control-Allow-Origin' => $allowedOrigin,
					'Access-Control-Allow-Methods' => 'OPTIONS, GET, POST, DELETE',
					'Access-Control-Allow-Headers' => 'Content-Type',
					'Access-Control-Allow-Credentials' => 'false',
				]);
				return $response;
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
			} else {
				$response = new JSONResponse(['error' => 'Not found'], Response::NOT_FOUND);
			}
			return $response;
		} catch (\Throwable $ex) {
			if ($ex instanceof LoggedException) {
				$ex->log();
			}

			if ($ex instanceof HTTPException) {
				return $ex->getResponse(Env::get('Camagru', 'mode'));
			}

			if (Env::get('Camagru', 'mode') == 'debug') {
				$response = new JSONResponse(
					[
						'error' => 'Exception',
						'message' => $ex->getMessage(),
						'code' => $ex->getCode(),
						'file' => $ex->getFile(),
						'line' => $ex->getLine(),
						'trace' => $ex->getTrace(),
					],
					Response::INTERNAL_SERVER_ERROR,
				);
			} else {
				$response = new JSONResponse(['error' => 'Server error. Retry later.'], Response::INTERNAL_SERVER_ERROR);
			}
			return $response;
		}
	}
}
