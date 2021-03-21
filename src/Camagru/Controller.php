<?php namespace Camagru;

use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Exception\ValidateException;

abstract class Controller
{
	protected $request;
	protected $requestHeaders;
	protected $input;
	protected $auth;
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
			throw new ValidateException($this->request, $field);
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
			if (empty($field)) {
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
	 * @param array|int|null $headersOrCode An array of Headers or an HTTP Response Code
	 * @param integer $code
	 * @return Http\Response
	 */
	protected function json($body, $code = Response::OK, $headers = []): Response
	{
		return new JSONResponse($body, $code, $headers);
	}
}
