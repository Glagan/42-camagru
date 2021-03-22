<?php namespace Camagru;

interface Routable
{
	/**
	 * Add a GET route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function get(string $path, array $route): void;

	/**
	 * Add a POST route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function post(string $path, array $route): void;

	/**
	 * Add a PUT route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function put(string $path, array $route): void;

	/**
	 * Add a PATCH route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function patch(string $path, array $route): void;

	/**
	 * Add a DELETE route.
	 * @see \Camagru\Router::add
	 * @param string $path
	 * @param array $route
	 * @return void
	 */
	public function delete(string $path, array $route): void;
}
