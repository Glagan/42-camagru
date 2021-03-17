<?php namespace Camagru;

use Camagru\Http\Response;
use Exception\ValidateException;

abstract class Controller
{
	protected $request;
	protected $requestHeaders;
	protected $input;

	public function __construct($request)
	{
		$this->request = $request;
		$this->requestHeaders = $request->getHeaders();
		$this->input = $request->getInput();
	}

	/**
	 * Check if $field exist in the request input and return it's value or false.
	 * @param string $field
	 * @return void
	 */
	private function inputFieldValue(string $field, $validator)
	{
		$value = $this->input->get($field);
		// The boolean false can't be received as a *real* value
		//	since values in the input are received as string
		if ($value === false) {
			throw new ValidateException($this->request, $field, $validator);
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
			if (empty($validator)) {
				if (!$this->inputFieldValue($field, $validator)) {
					throw new ValidateException($this->request, "Missing {$field}.");
				}
			}
			// If it's an array, check all parameters
			else if (\is_array($validator)) {
				$optional = isset($validator['optional']) ? $validator['optional'] : false;
				$value = $this->input->get($field);
				// Presence
				if ($value === false && !$optional) {
					throw new ValidateException($this->request, "Missing {$field}.");
				} else {
					// Validity
					if (\array_key_exists('validate', $validator)) {
						if (!\filter_var($value, $validator['validate'])) {
							throw new ValidateException($this->request, "Invalid {$field}.");
						}
					} else if (\array_key_exists('match', $validator)) {
						if (!\preg_match($validator['match'], $value)) {
							throw new ValidateException($this->request, "Invalid {$field}.");
						}
					}
					// Length
					if (\array_key_exists('min', $validator) && \mb_strlen($value) < $validator['min']) {
						throw new ValidateException($this->request, "{$field} is too short, must be longer than {$validator['min']}.");
					}
					if (\array_key_exists('max', $validator) && \mb_strlen($value) > $validator['max']) {
						throw new ValidateException($this->request, "{$field} is too long, must be smaller than {$validator['max']}.");
					}
				}
			}
			// If it's not an array, check for equaliy with the value
			else {
				$value = $this->inputFieldValue($field, $validator);
				if ($value !== $validator) {
					throw new ValidateException($this->request, "{$field} does not match.");
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
	protected function json($body, $headersOrCode = null, $code = Response::OK): Response
	{
		$headers = [];
		if ($headersOrCode !== null) {
			if (\is_array($headersOrCode)) {
				$headers = $headersOrCode;
			} else {
				$code = $headersOrCode;
			}
		}
		return new Response($body, $headers, $code, $this->request);
	}
}
