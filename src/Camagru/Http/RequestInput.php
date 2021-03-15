<?php namespace Camagru\Http;

class RequestInput
{
	protected $values;

	public function __construct(string $method, HeaderList $headers)
	{
		$this->values = [];
		// Get and filter query params
		$getInput = \filter_input_array(INPUT_GET, FILTER_DEFAULT, true);
		if (\is_array($getInput)) {
			foreach ($getInput as $name => $value) {
				$this->values[$name] = $value;
			}
		}

		if ($method != 'GET') {
			// Add decoded JSON input
			if ($headers->has(Header::CONTENT_TYPE, Header::JSON_TYPE)) {
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
}
