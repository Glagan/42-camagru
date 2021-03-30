<?php namespace Camagru;

use Env;
use Log;
use Models\User;

class Mail
{
	/**
	 * Send an Email.
	 * Body as an array represent each line separated by a `<br />`.
	 * @param string $to
	 * @param string $subject
	 * @param string|array $body
	 * @return bool
	 */
	public static function send(
		User $to,
		string $subject,
		$body
	) {

		$body = [
			"Messages" => [
				[
					"From" => [
						"Email" => Env::get('MailJet', 'sender_email'),
						"Name" => "Camagru",
					],
					"To" => [
						[
							"Email" => $to->email,
							"Name" => $to->username,
						],
					],
					"Subject" => $subject,
					"HTMLPart" => \is_array($body) ? \implode('<br />', $body) : $body,
				],
			],
		];

		// Create a cURL handler
		$ch = \curl_init('https://api.mailjet.com/v3.1/send');

		// SSL
		\curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		\curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		//\curl_setopt($ch, CURLOPT_CAINFO, '/usr/local/etc/php/cacert-2021-01-19.pem');

		// Debug
		$curlLog = false;
		if (Env::get('Camagru', 'mode') == 'debug') {
			\curl_setopt($ch, CURLOPT_VERBOSE, 2);
			$curlLog = \fopen(Env::get('Camagru', 'storage') . '/logs/curl.log', 'a+');
			\curl_setopt($ch, CURLOPT_STDERR, $curlLog);
		} else {
			\curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		}

		// Request
		$requestHeaders['Content-Type'] = 'application/json';
		$payload = \json_encode($body);
		$requestHeaders['Content-Length'] = \strlen($payload);
		\curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		\curl_setopt($ch, CURLOPT_POST, true);
		\curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			\array_map(
				function ($key, $value) {
					return $key . ': ' . $value;
				},
				array_keys($requestHeaders),
				$requestHeaders
			)
		);
		\curl_setopt($ch, CURLOPT_USERPWD, Env::get('MailJet', 'api_key') . ':' . Env::get('MailJet', 'secret_key'));

		// Response
		\curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		\curl_setopt($ch, CURLOPT_HEADER, false);
		$responseHeaders = [];
		// @source https://stackoverflow.com/a/41135574
		\curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$responseHeaders) {
			$len = \strlen($header);
			$header = \explode(':', $header, 2);
			if (\count($header) < 2) {
				// ignore invalid headers
				return $len;
			}
			$responseHeaders[\strtolower(\trim($header[0]))][] = \trim($header[1]);
			return $len;
		});

		// Send the request !
		$response = \curl_exec($ch);
		$details = \curl_getinfo($ch);

		// Check errors
		$errno = \curl_errno($ch);
		if ($errno) {
			Log::debug('cURL error: ' . $errno, \curl_error($ch));
			if ($curlLog !== false) {
				\fclose($curlLog);
			}
			\curl_close($ch);
			return false;
		}

		// Close resources
		if ($curlLog !== false) {
			\fclose($curlLog);
		}
		\curl_close($ch);

		// Verify response
		$code = $details['http_code'];
		$response = \json_decode($response, true);
		if ($code != 200) {
			return false;
		}

		return true;
	}
}
