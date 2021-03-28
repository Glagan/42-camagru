<?php namespace Camagru;

use Camagru\Http\Request;
use Log;

class Router implements Routable
{
	/**
	 * @var string
	 */
	protected $basePath;
	/**
	 * @var \Camagru\Route[]
	 */
	protected $routes;

	public function __construct(string $basePath = '')
	{
		$this->basePath = \trim($basePath, "/\r\n");
		if (\strlen($this->basePath) > 0) {
			$this->basePath .= '/';
		}
		$this->routes = [];
	}

	/**
	 * Add a route to the register routes.
	 * $path format: "/any/path/{parameter}/{parameter:.*}"
	 * 	If not regex is set for a parameter, "\d+" is assumed
	 * route keys
	 * @see \Camagru\Route::constructor
	 * $options['user']: required, string, Controler@function
	 * $options['auth']: optional, bool
	 * $options['noPrefix']: optional, bool
	 * @param string $method
	 * @param string $path
	 * @param array $options
	 * @return void
	 */
	private function add(string $method, string $path, array $options): void
	{
		if (!isset($options['use'])) {
			Log::debug("Route without a Controller [{$method}] {$path}");
			return;
		}
		$this->routes[] = new Route($method, $path, $options['use'], $options);
	}

	/**
	 * Create a RouterGroup and assign this router to create routes in a namespace.
	 * @param string $controller
	 * @param \Closure $callback
	 * @return void
	 */
	public function group(string $controller, \Closure $callback)
	{
		$group = new RouteGroup($this, $controller);
		$callback($group);
	}

	/**
	 * Add a GET route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function get(string $path, array $route): void
	{
		$this->add('GET', $path, $route);
	}

	/**
	 * Add a POST route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function post(string $path, array $route): void
	{
		$this->add('POST', $path, $route);
	}

	/**
	 * Add a PUT route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function put(string $path, array $route): void
	{
		$this->add('PUT', $path, $route);
	}

	/**
	 * Add a PATCH route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function patch(string $path, array $route): void
	{
		$this->add('PATCH', $path, $route);
	}

	/**
	 * Add a DELETE route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function delete(string $path, array $route): void
	{
		$this->add('DELETE', $path, $route);
	}

	/**
	 * Loop trough all added routes and check them against the Request::uri.
	 * Returns the found route or false on error.
	 * Returned route has a 'foundParams' key with all matched parameters in the URI.
	 * @return \Camagru\Route|false
	 */
	public function match(Request $request)
	{
		foreach ($this->routes as $route) {
			if ($route->match($this->basePath, $request)) {
				return $route;
			}
		}
		return false;
	}
}
