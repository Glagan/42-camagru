<?php namespace Camagru;

interface Routable
{
	/**
	 * Add a GET route.
	 * @see \Camagru\Router::add
	 */
	public function get(string $path, array $route);

	/**
	 * Add a POST route.
	 * @see \Camagru\Router::add
	 */
	public function post(string $path, array $route);

	/**
	 * Add a PUT route.
	 * @see \Camagru\Router::add
	 */
	public function put(string $path, array $route);

	/**
	 * Add a PATCH route.
	 * @see \Camagru\Router::add
	 */
	public function patch(string $path, array $route);

	/**
	 * Add a DELETE route.
	 * @see \Camagru\Router::add
	 */
	public function delete(string $path, array $route);
}
