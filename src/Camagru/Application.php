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
	/**
	 * @var \Camagru\Router
	 */
	private $router;

	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * Process the Request and return a Response.
	 * @param \Camagru\Http\Request $request
	 * @return \Camagru\Http\JSONResponse
	 */
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
			$route = $this->router->match($request);
			if ($route !== false) {
				// Check if the route need authentication
				$auth = new Auth();
				if (($route->requireAuth() && !$auth->isLoggedIn()) || ($route->rejectAuth() && $auth->isLoggedIn())) {
					$reason = $route->requireAuth() ?
					'You need to be logged in to access this page.' :
					'You need to be logged out to access this page.';
					throw new AuthException($reason);

				}

				// Call the route
				$controller = $route->getController();
				$controller = new $controller($request, $auth);
				$response = \call_user_func([$controller, $route->getFunction()], ...$route->getFoundParams());
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
