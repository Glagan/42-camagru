<?php namespace Camagru\Http;

use Env;

/**
 * Find and normalize all requested inputs from a received request.
 */
class Request
{
	/**
	 * @var string
	 */
	protected $method;
	/**
	 * @var string
	 */
	protected $uri;
	/**
	 * @var \Camagru\Http\HeaderList
	 */
	protected $headers;
	/**
	 * @var \Camagru\Http\RequestInput
	 */
	protected $input;
	/**
	 * @var bool
	 */
	protected $isSecure;

	public function __construct()
	{
		$this->method = \strtoupper(\filter_input(\INPUT_SERVER, 'REQUEST_METHOD'));
		$this->uri = \filter_input(\INPUT_SERVER, 'REQUEST_URI');
		$this->uri = \trim(\parse_url($this->uri)['path'], '/');
		$this->headers = new HeaderList;
		$headers = \filter_var_array(\apache_request_headers(), \FILTER_DEFAULT);
		foreach ($headers as $name => $value) {
			$this->headers->add($name, $value);
		}
		$this->input = new RequestInput($this->method, $this->headers);
		$this->isSecure =
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| $_SERVER['SERVER_PORT'] == 443;
	}

	/**
	 * Create a Request object from globals.
	 * @return Request
	 */
	public static function make(): Request
	{
		return new Request;
	}

	/**
	 * Request method.
	 * GET POST PUT PATCH DELETE
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * Request URI.
	 * @return string
	 */
	public function getUri(): string
	{
		return $this->uri;
	}

	/**
	 * Request URI without the given basePath
	 * @param string $basePath
	 * @return string
	 */
	public function getLocalUri(string $basePath): string
	{
		return \str_replace($basePath, '', $this->uri);
	}

	/**
	 * Request Headers as an HeaderList
	 * @return HeaderList
	 */
	public function getHeaders(): HeaderList
	{
		return $this->headers;
	}

	/**
	 * Request input from all sources.
	 * @return RequestInput
	 */
	public function getInput(): RequestInput
	{
		return $this->input;
	}

	/**
	 * Find the complete requested URI with the HTTP Scheme, Host and port when necessary.
	 * @return string
	 */
	public function getCompleteUri(): string
	{
		$scheme = $this->isSecure ? 'https' : 'http';
		$port = $_SERVER['SERVER_PORT'];
		$host = $_SERVER['HTTP_HOST'];
		if (!$host) {
			$host = $_SERVER['SERVER_NAME'];
		}
		if (!$host) {
			$host = $_SERVER['SERVER_ADDR'];
		}

		if (($port == 80 && $scheme == 'http') || ($port == 443 && $scheme == 'https')) {
			return $scheme . '://' . $host;
		}
		return $scheme . '://' . $host . ':' . $port;
	}

	/**
	 * Check if the request has an origin and if it's for the current host.
	 * @return boolean
	 */
	public function isCors(): bool
	{
		$origin = $this->headers->get('Origin');
		if ($origin !== false) {
			$host = $this->getCompleteUri();
			return $origin !== $host;
		}
		return false;
	}

	/**
	 * Check if the requested Origin is whitelisted and return it.
	 * If not, returns the configured URL in config/camagru.php
	 * @return string
	 */
	public function resolveAllowedOrigin(): string
	{
		if (Env::get('Camagru', 'origin_whitelist') !== false) {
			$whitelist = Env::get('Camagru', 'origin_whitelist', []);
			$origin = $this->headers->get('Origin');
			if (\is_array($whitelist) && \in_array($origin, $whitelist)) {
				return $origin;
			}
		}
		return Env::get('Camagru', 'url', '');
	}
}
