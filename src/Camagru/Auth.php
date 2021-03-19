<?php namespace Camagru;

use Models\User;
use Models\UserSession;
use SQL\Operator;
use SQL\Value;

class Auth
{
	private $user = null;

	public function __construct()
	{
		// Check if there is an entry for the session in the UserSession table
		$session = \session_id();
		$userSession = UserSession::first([
			'session' => $session,
			// Session issued only less than 7 days ago are valid
			['issued', Operator::MORE_THAN, Value::make("(NOW() - INTERVAL 7 DAY)")],
		]);
		if ($userSession !== false) {
			$this->user = User::get($userSession->user);
		}
	}

	public function getUser()
	{
		if ($this->user !== null) {
			return $this->user;
		}
		return false;
	}

	public function isLoggedIn(): bool
	{
		return $this->user !== null;
	}
}
