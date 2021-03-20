<?php namespace Camagru\Http;

use Env;

/**
 * Find and normalize all requested inputs from a received request.
 * @property string $method
 * @property string $uri
 * @property \Camagru\Http\HeaderList $headers
 * @property \Camagru\Http\RequestInput $input
 */
class Request
{
	protected $method;
	protected $uri;
	protected $headers;
	protected $input;
	protected $isSecure;

	public function __construct()
	{
		$this->method = \strtoupper(\filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
		$this->uri = \filter_input(INPUT_SERVER, 'REQUEST_URI');
		$this->uri = \trim(\parse_url($this->uri)['path'], '/');
		$this->headers = new HeaderList;
		$headers = \filter_var_array(\apache_request_headers(), FILTER_DEFAULT);
		foreach ($headers as $name => $value) {
			$this->headers->add($name, $value);
		}
		$this->input = new RequestInput($this->method, $this->headers);
		$this->isSecure =
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| $_SERVER['SERVER_PORT'] == 443;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getUri(): string
	{
		return $this->uri;
	}

	public function getLocalUri($basePath): string
	{
		return \str_replace($basePath, '', $this->uri);
	}

	public function getHeaders(): HeaderList
	{
		return $this->headers;
	}

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
