<?php namespace Models;

use Camagru\Model;
use Log;
use SQL\Operator;
use SQL\Value;

/**
 * Valid User sessions.
 * @property int $id
 * @property int $user
 * @property string $session
 * @property \DateTime $issued
 * @property bool $rememberMe
 */
class UserSession extends Model
{
	/**
	 * @var array
	 */
	protected static $fields = [
		'user',
		'session',
		'issued',
		'rememberMe',
	];

	/**
	 * @var array
	 */
	protected static $casts = [
		'user' => User::class,
		'issued' => 'date',
		'rememberMe' => 'bool',
	];

	/**
	 * Find the first valid session for the given session ID and returns it or false if there is none.
	 * @param string $session
	 * @return \Models\UserSession|false
	 */
	public static function firstValid(string $session)
	{
		$result = static::first([
			'session' => $session,
			[
				[
					'rememberMe' => true,
					'issued' => [Operator::MORE_OR_EQUAL, Value::make("(NOW() - INTERVAL 1 YEAR)")],
				],
				Operator::CONDITION_OR,
				'issued' => [Operator::MORE_OR_EQUAL, Value::make("(NOW() - INTERVAL 1 HOUR)")],
			],
		]);
		return $result;
	}

	/**
	 * Update the session cookie on login and register with a different lifetime.
	 * @param int $lifetime
	 * @return void
	 */
	private function setSessionCookie(\Camagru\Http\Request $request, string $session, int $lifetime): void
	{
		$setCookie = \setcookie('session', $session, [
			'expires' => time() + $lifetime,
			'path' => '/',
			'domain' => $request->getCookieDomain(),
			'secure' => $request->isSecure(),
			'httponly' => true,
			'samesite' => 'Strict',
		]);
		if ($setCookie === false) {
			Log::debug('Failed to set Session Cookie: ' . $session . '  # ' . $lifetime);
		}
	}

	/**
	 * Set the Cookie for the current UserSession.
	 * Set 1 year if remember me is enabled.
	 * @return void
	 */
	public function setCookie(\Camagru\Http\Request $request): void
	{
		if ($this->rememberMe) {
			$this->setSessionCookie($request, $this->session, 60 * 60 * 24 * 365); // 1 year
		} else {
			$this->setSessionCookie($request, $this->session, 60 * 60); // 1 hour
		}
	}

	/**
	 * Update the issued date for the current UserSession and persist to the Database.
	 * @return void
	 */
	public function refresh(): void
	{
		$this->issued = new \DateTime();
		$this->persist();
	}
}
