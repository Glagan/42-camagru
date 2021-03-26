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
	 * @var array
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
	 * [
	 * 	use: "Controller@function"
	 * 	auth: boolean or null
	 * ]
	 * Auth set to true or false require be to logged in or not.
	 * If Auth is omitted or null the login state is not checked.
	 * @param string $method
	 * @param string $path
	 * @param string $callback
	 * @return void
	 */
	private function add(string $method, string $path, array $route): void
	{
		if (!isset($route['use'])) {
			Log::debug("Route without a Controller [{$method}] {$path}");
			return;
		}
		$callback = \explode('@', $route['use']);
		if (\count($callback) !== 2) {
			Log::debug("Route with invalid Controller [{$method}] {$path} {$route['use']}");
			return;
		}
		$path = \trim($path, '/');
		$params = Router::pathParams($path);

		$this->routes[] = [
			'method' => $method,
			'path' => $path,
			'params' => $params,
			'auth' => isset($route['auth']) ? $route['auth'] : null,
			'regex' => Router::pathToRegex($path, $params),
			'controller' => $callback[0],
			'function' => $callback[1],
			'noPrefix' => isset($route['noPrefix']) ? $route['noPrefix'] : false,
		];
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
	 * @return array|false
	 */
	public function match(Request $request)
	{
		$uri = $request->getUri();
		foreach ($this->routes as $route) {
			$match = [];
			if ($route['noPrefix']) {
				$regex = "#^{$route['regex']}$#";
			} else {
				$regex = "#^{$this->basePath}{$route['regex']}$#";
			}
			if ($request->getMethod() === $route['method'] && \preg_match($regex, $uri, $match)) {
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
