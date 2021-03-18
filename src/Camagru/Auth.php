<?php namespace Camagru;

use Models\User;
use Models\UserSession;

class Auth
{
	private $user = null;

	public function __construct()
	{
		// Check if there is an entry for the session in the UserSession table
		//	Session issued only less than 7 days ago are valid
		$session = \session_id();
		$userSession = UserSession::where([['session', $session], ['issued', '<', 'INTERVAL 7 DAY']]);
		if (\count($userSession) > 0) {
			$this->user = User::get($userSession[0]->user);
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
