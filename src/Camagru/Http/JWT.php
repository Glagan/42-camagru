<?php namespace Camagru\Http;

use DateTime;
use Env;
use Exception\JSONException;
use Exception\JWTException;

/**
 * Simple implement of JSON Web Token.
 * Only support the HS256 algorithm.
 * @see https://tools.ietf.org/html/rfc7519
 */
class JWT
{
	const ALGORITHM = 'HS256';
	const ALGORITHM_FUNCTION = 'SHA256';
	// Leeway for tokens expiration in seconds - 60s by default
	const LEEWAY = 60;

	/**
	 * Create JWT with the given payload.
	 * @param array $payload
	 * @return string
	 */
	public static function encode(array $payload): string
	{
		// Create JOSE Header
		$header = ['typ' => 'JWT', 'alg' => JWT::ALGORITHM];
		$parts = [];
		$parts[] = static::base64Encode(static::jsonEncode($header));
		// Add issued at to payload if it doesn't already exists
		if (!isset($payload['iat'])) {
			$payload['iat'] = (new DateTime)->format('U');
		}
		$parts[] = static::base64Encode(static::jsonEncode($payload));

		// Sign the Header + Payload
		$firstParts = \implode('.', $parts);
		$parts[] = static::base64Encode(static::sign($firstParts));

		return \implode('.', $parts);
	}

	/**
	 * Base64 url variant safe for URLs.
	 * @see https://www.php.net/manual/en/function.base64-encode.php#121767
	 * @param mixed $value
	 * @return string
	 */
	private static function base64Encode($value): string
	{
		return \rtrim(\strtr(\base64_encode($value), '+/', '-_'), '=');
	}

	/**
	 * Wrapper around \json_encode to throw Exceptions.
	 * @param mixed $value Any JSON valid value
	 * @return string
	 */
	private static function jsonEncode($value): string
	{
		$result = \json_encode($value, 0, 64);
		$error = \json_last_error();
		if ($result === false || $error !== \JSON_ERROR_NONE) {
			throw new JSONException($error);
		}
		return $result;
	}

	/**
	 * Only the HS256 algorithm is supported.
	 * Encrypt a payload using a secret key.
	 * @param string $message
	 * @return string
	 */
	public static function sign(string $message): string
	{
		return \hash_hmac(static::ALGORITHM_FUNCTION, $message, Env::get('Camagru', 'secret_key'), true);
	}

	/**
	 * Decode a given JWT Token.
	 * @param string $token
	 * @return mixed Any valid JSON value
	 */
	public static function decode(string $token)
	{
		// Explode parts with a . delimiter
		//	All parts are b64 encoded
		$parts = \explode('.', $token);
		if (\count($parts) !== 3) {
			throw new JWTException($token, 'Wrong number of parts.');
		}
		// Decode and verify each parts
		[$header64, $payload64, $signature64] = $parts;
		// Header
		$header = static::jsonDecode(static::base64Decode($header64));
		if ($header === null) {
			throw new JWTException($token, 'Invalid Header.');
		}
		if (!isset($header['alg'])) {
			throw new JWTException($token, 'Missing Algorithm.');
		}
		if ($header['alg'] !== JWT::ALGORITHM) {
			throw new JWTException($token, 'Algorithm not supported. Only HS256 is supported.');
		}
		// Signature -- before payload to avoid decoding an invalid object
		$signature = static::base64Decode($signature64);
		if ($signature === false || !static::verify("{$header64}.{$payload64}", $signature)) {
			throw new JWTException($token, 'Invalid Signature.');
		}
		// Payload
		$payload = static::jsonDecode(static::base64Decode($payload64));
		if ($payload === null) {
			throw new JWTException($token, 'Invalid Payload.');
		}
		// Not Before
		if (isset($payload['nbf']) && $payload['nbf'] > ((new DateTime)->format('U') + JWT::LEEWAY)) {
			throw new JWTException($token, 'Token not active.');
		}
		// Check Issued at if it exists -- the same way nbf works
		if (isset($payload['iat']) && $payload['iat'] > ((new DateTime)->format('U') + JWT::LEEWAY)) {
			throw new JWTException($token, 'Token not active.');
		}
		// Expiration -- $now = (new DateTime)->format('U');
		if (isset($payload['exp']) && $payload['exp'] < ((new DateTime)->format('U') + JWT::LEEWAY)) {
			throw new JWTException($token, 'Token expired.');
		}
		return $payload;
	}

	/**
	 * Base64 url variant safe for URLs.
	 * @see https://www.php.net/manual/en/function.base64-encode.php#121767
	 * @param string $value
	 * @return mixed
	 */
	private static function base64Decode(string $value)
	{
		return \base64_decode(\strtr($value, '-_', '+/') . \str_repeat('=', 3 - (3+\strlen($value)) % 4));
	}

	/**
	 * Wrapper around \json_decode to throw Exceptions.
	 * @param string $value An encoded JSON string
	 * @return string
	 */
	private static function jsonDecode($value)
	{
		$result = \json_decode($value, true, 64);
		$error = \json_last_error();
		if ($result === false || $error !== \JSON_ERROR_NONE) {
			throw new JSONException($error);
		}
		return $result;
	}

	/**
	 * Verify that the given message equals to the given signature.
	 * Only HS256 is supported so the message is simply signed and compared.
	 * @param string $message
	 * @param string $signature
	 * @return boolean
	 */
	public static function verify(string $message, string $signature): bool
	{
		return \hash_equals($signature, static::sign($message));
	}

}
