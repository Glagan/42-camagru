<?php namespace Camagru;

use Camagru\Http\Request;
use Exception;

class Router
{
	protected $basePath;
	protected $routes;

	public function __construct(string $basePath = '')
	{
		$this->basePath = $basePath;
		$this->routes = [];
	}

	/**
	 * Add a route to the register routes.
	 * $path format: "/any/path/{parameter}/{parameter:.*}"
	 * 	If not regex is set for a parameter, "\d+" is assumed
	 * $callback format: "Controller@function"
	 *
	 * @param string $method
	 * @param string $path
	 * @param string $callback
	 *
	 * @return void
	 */
	private function add(string $method, string $path, string $callback): void
	{
		$callback = \explode('@', $callback);
		if (\count($callback) !== 2) {
			throw new Exception();
		}
		$path = \trim($path, '/');
		$params = Router::pathParams($path);

		$this->routes[] = [
			'method' => $method,
			'path' => $path,
			'params' => $params,
			'regex' => Router::pathToRegex($path, $params),
			'controller' => $callback[0],
			'function' => $callback[1],
		];
	}

	/**
	 * Add an OPTIONS route.
	 * @see \Camagru\Router::add
	 */
	public function options(string $path, $callback)
	{
		$this->add('OPTIONS', $path, $callback);
	}

	/**
	 * Add a GET route.
	 * @see \Camagru\Router::add
	 */
	public function get(string $path, $callback)
	{
		$this->add('GET', $path, $callback);
	}

	/**
	 * Add a POST route.
	 * @see \Camagru\Router::add
	 */
	public function post(string $path, $callback)
	{
		$this->add('POST', $path, $callback);
	}

	/**
	 * Add a PUT route.
	 * @see \Camagru\Router::add
	 */
	public function put(string $path, $callback)
	{
		$this->add('PUT', $path, $callback);
	}

	/**
	 * Add a PATCH route.
	 * @see \Camagru\Router::add
	 */
	public function patch(string $path, $callback)
	{
		$this->add('PATCH', $path, $callback);
	}

	/**
	 * Add a DELETE route.
	 * @see \Camagru\Router::add
	 */
	public function delete(string $path, $callback)
	{
		$this->add('DELETE', $path, $callback);
	}

	/**
	 * Loop trough all added routes and check them against the Request::uri.
	 * Returns the found route or false on error.
	 * Returned route has a 'foundParams' key with all matched parameters in the URI.
	 */
	public function match(Request $request)
	{
		$localUri = $request->getLocalUri($this->basePath);
		foreach ($this->routes as $route) {
			$match = [];
			if ($request->getMethod() === $route['method'] && \preg_match("#^{$route['regex']}$#", $localUri, $match)) {
				$route['foundParams'] = Router::cleanMatches($route['params'], $match);
				return $route;
			}
		}
		return false;
	}

	/**
	 * Find and return all parameters in the given route.
	 * The route is simply exploded by the '/' delimiter.
	 * Each found parameters are check for query parameters.
	 * Returns an array of ['name', 'regex'] of each found query parameters.
	 * @param string $route
	 * @return array
	 */
	private static function pathParams(string $route): array
	{
		$params = \explode('/', $route);
		$foundParams = [];
		foreach ($params as $value) {
			$openDelimiter = \strpos($value, '{');
			$closeDelimiter = \strpos($value, '}', $openDelimiter);

			// Add a regex param if there is a "{}" delimiter
			if ($openDelimiter >= 0 && $closeDelimiter > $openDelimiter) {
				// Remove the delimiter
				$value = \substr($value, $openDelimiter + 1, $closeDelimiter - $openDelimiter - 1);

				// Set the regex for each params
				// 	Default regex is \d+ for a number
				$paramValues = \explode(':', $value);
				if (\count($paramValues) != 2) {
					$paramValues[1] = '\d+';
				}
				$foundParams[] = [
					'name' => $paramValues[0],
					'regex' => $paramValues[1],
				];
			}
		}
		return $foundParams;
	}

	/**
	 * Replace {name} and {name:regex} occurences in path to a regex.
	 * @param string $path
	 * @return string
	 */
	private static function pathToRegex(string $path, array $params): string
	{
		$regexRoute = $path;
		foreach ($params as $value) {
			// Replace each params with its regex
			$modified = 0;
			$regexRoute = \str_replace("{{$value['name']}}", "({$value['regex']})", $regexRoute, $modified);
			// If a custom regex was sets
			if ($modified == 0) {
				$regexRoute = \str_replace("{{$value['name']}:{$value['regex']}}", "({$value['regex']})", $regexRoute, $modified);
			}
		}
		return $regexRoute;
	}

	/**
	 * Filter and clean found query parameters from the Regex match array.
	 * Return a new cleaned match array.
	 *
	 * @param array $params The matched route query parameters
	 * @param array $match Query paremeters found in the route regex
	 * @return array
	 */
	private static function cleanMatches(array $params, array $match): array
	{
		// Delete the global match
		\array_shift($match);

		// Clean the array
		$matches = [];
		foreach ($match as $key => $value) {
			if (\strpos($params[$key]['regex'], '\d') !== false) {
				$matches[] = \filter_var($value, FILTER_VALIDATE_INT);
			} else {
				$matches[] = \filter_var($value, FILTER_DEFAULT);
			}
		}

		return $matches;
	}
}
