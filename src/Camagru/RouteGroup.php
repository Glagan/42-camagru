<?php namespace Camagru;

class RouteGroup implements Routable
{
	private $router;
	private $controller;
	private $prefix;

	public function __construct(Router $router, string $controller, string $prefix = '')
	{
		$this->router = $router;
		$this->controller = $controller;
		$this->prefix = $prefix;
	}

	private function updateRoute(&$path, array &$route): void
	{
		if (isset($route['use'])) {
			$route['use'] = "{$this->controller}@{$route['use']}";
			$path = $this->prefix . $path;
		}
	}

	public function get(string $path, array $route)
	{
		$this->updateRoute($path, $route);
		$this->router->get($path, $route);
	}

	public function post(string $path, array $route)
	{
		$this->updateRoute($path, $route);
		$this->router->post($path, $route);
	}

	public function put(string $path, array $route)
	{
		$this->updateRoute($path, $route);
		$this->router->put($path, $route);
	}

	public function patch(string $path, array $route)
	{
		$this->updateRoute($path, $route);
		$this->router->patch($path, $route);
	}

	public function delete(string $path, array $route)
	{
		$this->updateRoute($path, $route);
		$this->router->delete($path, $route);
	}
}
