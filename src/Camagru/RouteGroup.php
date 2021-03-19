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

	private function updateRoute(array &$route): void
	{
		$route['use'] = "{$this->controller}@{$route['use']}";
		$route['path'] = $this->prefix . $route['path'];
	}

	public function get(string $path, array $route)
	{
		$this->updateRoute($route);
		$this->router->get($path, $route);
	}

	public function post(string $path, array $route)
	{
		$this->updateRoute($route);
		$this->router->post($path, $route);
	}

	public function put(string $path, array $route)
	{
		$this->updateRoute($route);
		$this->router->put($path, $route);
	}

	public function patch(string $path, array $route)
	{
		$this->updateRoute($route);
		$this->router->patch($path, $route);
	}

	public function delete(string $path, array $route)
	{
		$this->updateRoute($route);
		$this->router->delete($path, $route);
	}
}
