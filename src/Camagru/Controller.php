<?php namespace Camagru;

use Camagru\Http\FileResponse;
use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Exception\ValidateException;

abstract class Controller
{
	/**
	 * @var \Camagru\Http\Request
	 */
	protected $request;
	/**
	 * @var \Camagru\Http\HeaderList
	 */
	protected $requestHeaders;
	/**
	 * @var \Camagru\Http\RequestInput
	 */
	protected $input;
	/**
	 * @var \Camagru\Auth
	 */
	protected $auth;
	/**
	 * @var \Models\User|null
	 */
	protected $user;

	public function __construct($request, $auth)
	{
		$this->request = $request;
		$this->requestHeaders = $request->getHeaders();
		$this->input = $request->getInput();
		$this->auth = $auth;
		$this->user = $auth !== null ? $auth->getUser() : null;
	}

	/**
	 * Check if $field exist in the request input and return it's value or false.
	 * @param string $field
	 * @return void
	 */
	private function inputFieldValue(string $field)
	{
		$value = $this->input->get($field);
		// The boolean false can't be received as a *real* value
		//	since values in the input are received as string
		if ($value === false) {
			throw new ValidateException($field);
		}
		return $value;
	}

	/**
	 * Validate all given $validators or throw on error.
	 * Validator format:
	 * [key] as the field name
	 * If the key has no body only the presence is tested.
	 * If the key is not an array, equality is tested.
	 * [
	 *	optional?: boolean
	 *	match: string, Regex
	 *	validate: FILTER_VALIDATE_* flag
	 *	min?: Minimum length
	 *	max?: Maximum length
	 * ]
	 * @param array $validators
	 * @return bool
	 */
	protected function validate($validators): bool
	{
		foreach ($validators as $field => $validator) {
			// If the validator is a simple key, only check for presence
			if (!\is_string($field)) {
				if (!$this->inputFieldValue($validator)) {
					throw new ValidateException("Missing {$validator}.");
				}
			}
			// If it's an array, check all parameters
			else if (\is_array($validator)) {
				$optional = isset($validator['optional']) ? $validator['optional'] : false;
				if (!$optional && isset($validator['requiredIf'])) {
					$optional = $this->input->get($validator['requiredIf']) === false;
				}
				$value = $this->input->get($field);
				// Presence
				if ($value === false) {
					if (!$optional) {
						throw new ValidateException("Missing {$field}.");
					}
				} else {
					// Type
					if (isset($validator['type'])) {
						if ($validator['type'] == 'array' && !\is_array($value)) {
							throw new ValidateException("{$field} need to be an array.");
						} else if ($validator['type'] == 'string' && !\is_string($value)) {
							throw new ValidateException("{$field} need to be a string.");
						} else if ($validator['type'] == 'int' && !\is_numeric($value)) {
							throw new ValidateException("{$field} need to be an integer.");
						}
					}
					// Validity
					if (isset($validator['validate'])) {
						if (!\filter_var($value, $validator['validate'])) {
							throw new ValidateException("Invalid {$field}.");
						}
					} else if (isset($validator['match'])) {
						[$regex, $message] = $validator['match'];
						if (!\preg_match($regex, $value)) {
							throw new ValidateException($message);
						}
					}
					// Length
					if (isset($validator['min']) && \mb_strlen($value) < $validator['min']) {
						throw new ValidateException("{$field} is too short, must be longer than {$validator['min']}.");
					}
					if (isset($validator['max']) && \mb_strlen($value) > $validator['max']) {
						throw new ValidateException("{$field} is too long, must be smaller than {$validator['max']}.");
					}
				}
			}
			// If it's not an array, check for equaliy with the value
			else {
				$value = $this->inputFieldValue($field, $validator);
				if ($value !== $validator) {
					throw new ValidateException("{$field} does not match.");
				}

			}
		}
		return true;
	}

	/**
	 * Returns a JSON Response with $body as it's content.
	 * @param array $body
	 * @param int $code An HTTP Response Code
	 * @param array $headers
	 * @return Http\JSONResponse
	 */
	protected function json(array $body, $code = Response::OK, $headers = []): Response
	{
		return new JSONResponse($body, $code, $headers);
	}

	/**
	 * Returns a File Response with $body as the file content with the correct Content-Type
	 * @param array $body
	 * @param int $code An HTTP Response Code
	 * @param array $headers
	 * @return Http\FileResponse
	 */
	protected function file(string $path, $code = Response::OK, $headers = []): Response
	{
		return new FileResponse($path, $code, $headers);
	}
}
