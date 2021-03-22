<?php namespace Camagru;

class RouteGroup implements Routable
{
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var string
	 */
	private $controller;
	/**
	 * @var string
	 */
	private $prefix;

	public function __construct(Router $router, string $controller, string $prefix = '')
	{
		$this->router = $router;
		$this->controller = $controller;
		$this->prefix = $prefix;
	}

	/**
	 * Update the route path and ['use'] attribute to add the prefix and Controller.
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	private function updateRoute(string &$path, array &$route): void
	{
		if (isset($route['use'])) {
			$route['use'] = "{$this->controller}@{$route['use']}";
			$path = $this->prefix . $path;
		}
	}

	/**
	 * Add a GET route to the router.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function get(string $path, array $route): void
	{
		$this->updateRoute($path, $route);
		$this->router->get($path, $route);
	}

	/**
	 * Add a POST route to the router.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function post(string $path, array $route): void
	{
		$this->updateRoute($path, $route);
		$this->router->post($path, $route);
	}

	/**
	 * Add a PUT route to the router.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function put(string $path, array $route): void
	{
		$this->updateRoute($path, $route);
		$this->router->put($path, $route);
	}

	/**
	 * Add a PATCH route to the router.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function patch(string $path, array $route): void
	{
		$this->updateRoute($path, $route);
		$this->router->patch($path, $route);
	}

	/**
	 * Add a DELETE route to the router.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function delete(string $path, array $route): void
	{
		$this->updateRoute($path, $route);
		$this->router->delete($path, $route);
	}
}
