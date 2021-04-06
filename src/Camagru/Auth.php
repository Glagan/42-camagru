<?php namespace Camagru;

use Models\User;
use Models\UserSession;

class Auth
{
	/**
	 * @var \Models\User|null
	 */
	private $user = null;

	public function __construct()
	{
		// Check if there is an entry for the session in the UserSession table
		// Session with rememberMe or issued less than 1 hour ago are valid
		$session = \session_id();
		$userSession = UserSession::firstValid($session);
		if ($userSession !== false) {
			$userSession->refresh();
			$this->user = User::get($userSession->user);
		}
	}

	/**
	 * @return \Models\User|false
	 */
	public function getUser()
	{
		if ($this->user !== null) {
			return $this->user;
		}
		return false;
	}

	/**
	 * @return boolean
	 */
	public function isLoggedIn(): bool
	{
		return $this->user !== null;
	}
}
