<?php namespace Camagru;

use Camagru\Http\Request;

class Route
{
	/**
	 * @var string
	 */
	protected $method;
	/**
	 * @var string
	 */
	protected $path;
	/**
	 * @var array
	 */
	protected $params;
	/**
	 * @var bool|null
	 */
	protected $auth;
	/**
	 * @var string
	 */
	protected $regex;
	/**
	 * @var string|null
	 */
	protected $controller;
	/**
	 * @var string|null
	 */
	protected $function;
	/**
	 * @var bool
	 */
	protected $noPrefix;
	/**
	 * @var array
	 */
	protected $foundParams;

	/**
	 * $options['auth']: optional, bool
	 * 	Auth set to true or false require be to logged in or not.
	 * 	If Auth is omitted or null the login state is not checked.
	 * $options['noPrefix']: optional, bool
	 * 	Avoid adding basePath in match
	 * @param string $method
	 * @param string $path
	 * @param string $callback
	 * @param array $options
	 */
	public function __construct(string $method, string $path, string $callback, array $options)
	{
		$this->method = $method;
		$this->path = \trim($path, '/');
		$this->auth = $options['auth'];
		$callback = \explode('@', $callback);
		if (\count($callback) == 2) {
			$this->controller = $callback[0];
			$this->function = $callback[1];
		} else {
			$this->controller = null;
			$this->function = null;
		}
		$this->noPrefix = $options['noPrefix'];
		$this->initializeParams();
		$this->initializeRegex();
		$this->foundParams = [];
	}

	/**
	 * Find and return all parameters in the given route.
	 * The route is simply exploded by the '/' delimiter.
	 * Each found parameters are check for query parameters.
	 * Returns an array of ['name', 'regex'] of each found query parameters.
	 * @return void
	 */
	private function initializeParams(): void
	{
		$params = \explode('/', $this->path);
		$this->params = [];
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
				$this->params[] = [
					'name' => $paramValues[0],
					'regex' => $paramValues[1],
				];
			}
		}
	}

	/**
	 * Replace {name} and {name:regex} occurences in path to a regex.
	 * The route can contain other regex expressions which are ignored.
	 * @return void
	 */
	private function initializeRegex()
	{
		$this->regex = $this->path;
		foreach ($this->params as $value) {
			// Replace each params with its regex
			$modified = 0;
			$this->regex = \str_replace("{{$value['name']}}", "({$value['regex']})", $this->regex, $modified);
			// If a custom regex was set
			if ($modified == 0) {
				$this->regex = \str_replace("{{$value['name']}:{$value['regex']}}", "({$value['regex']})", $this->regex, $modified);
			}
		}
		return $this->regex;
	}

	public function match(string $basePath, Request $request): bool
	{
		$uri = $request->getUri();
		$match = [];
		if ($this->noPrefix) {
			$regex = "#^{$this->regex}$#";
		} else {
			$regex = "#^{$basePath}{$this->regex}$#";
		}
		if ($request->getMethod() === $this->method && \preg_match($regex, $uri, $match)) {
			$this->foundParams = $this->cleanMatches($match);
			return true;
		}
		return false;
	}

	/**
	 * Filter and clean found query parameters from the Regex match array.
	 * Return a new cleaned match array.
	 * @param array $match Query paremeters found in the route regex
	 * @return array
	 */
	private function cleanMatches(array $match): array
	{
		// Delete the global match
		\array_shift($match);
		// Clean the array
		$matches = [];
		foreach ($match as $key => $value) {
			if (\strpos($this->params[$key]['regex'], '\d') !== false) {
				$matches[] = \filter_var($value, FILTER_VALIDATE_INT);
			} else {
				$matches[] = \filter_var($value, FILTER_DEFAULT);
			}
		}
		return $matches;
	}

	/**
	 * Check if the Route has a valid Controller and function.
	 * @return boolean
	 */
	public function valid(): bool
	{
		return $this->controller !== null && $this->function !== null;
	}

	/**
	 * @return boolean
	 */
	public function requireAuth(): bool
	{
		return $this->auth === true;
	}

	/**
	 * @return boolean
	 */
	public function rejectAuth(): bool
	{
		return $this->auth === false;
	}

	/**
	 * @return string
	 */
	public function getController(): string
	{
		return $this->controller;
	}

	/**
	 * @return string
	 */
	public function getFunction(): string
	{
		return $this->function;
	}

	/**
	 * Return the cleaned params found after a match with the Request.
	 * @return array
	 */
	public function getFoundParams(): array
	{
		return $this->foundParams;
	}
}
