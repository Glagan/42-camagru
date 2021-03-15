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

	private function add(string $method, string $path, string $callback)
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

	public function options(string $path, $callback)
	{
		$this->add('OPTIONS', $path, $callback);
	}

	public function get(string $path, $callback)
	{
		$this->add('GET', $path, $callback);
	}

	public function post(string $path, $callback)
	{
		$this->add('POST', $path, $callback);
	}

	public function put(string $path, $callback)
	{
		$this->add('PUT', $path, $callback);
	}

	public function patch(string $path, $callback)
	{
		$this->add('PATCH', $path, $callback);
	}

	public function delete(string $path, $callback)
	{
		$this->add('DELETE', $path, $callback);
	}

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
				$value = \substr($value, $openDelimiter, $closeDelimiter - $openDelimiter);

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
