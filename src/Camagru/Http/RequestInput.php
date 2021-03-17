<?php namespace Camagru\Http;

class RequestInput
{
	protected $values;

	public function __construct(string $method, HeaderList $headers)
	{
		$this->values = [];

		// Get and filter query params
		if ($method === 'GET') {
			$getInput = \filter_input_array(INPUT_GET, FILTER_DEFAULT, true);
			if (\is_array($getInput)) {
				foreach ($getInput as $name => $value) {
					$this->values[$name] = $value;
				}
			}
		} else if ($method !== 'OPTIONS') {
			// Add decoded JSON input
			$contentType = $headers->get(Header::CONTENT_TYPE);
			if ($contentType !== false && \mb_strpos($contentType, Header::JSON_TYPE) === 0) {
				$content = \file_get_contents('php://input');
				if ($content != '') {
					$bodyInput = \filter_var_array(\json_decode($content, true), FILTER_DEFAULT, true);
				}
			}

			// Add raw POST input
			else {
				$bodyInput = \filter_input_array(INPUT_POST, FILTER_DEFAULT, true);
			}

			// Body overwrite query params
			if ($bodyInput && \is_array($bodyInput)) {
				foreach ($bodyInput as $name => $value) {
					$this->values[$name] = $value;
				}
			}
		}
	}

	/**
	 * Get the value associated with name in the Request input.
	 * Returns $default if the value doesn't exists.
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $name, $default = false)
	{
		if (\array_key_exists($name, $this->values)) {
			return $this->values[$name];
		}
		return $default;
	}
}
