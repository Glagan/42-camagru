<?php namespace Controller;

use Camagru\Controller;
use Models\User;
use Models\UserSession;
use SQL\Operator;

class Authentication extends Controller
{
	public function register()
	{
		// Validate Form Data
		$this->validate([
			'username' => [
				'min' => 4,
				'max' => 100,
			],
			'email' => [
				'validate' => \FILTER_VALIDATE_EMAIL,
			],
			// @see https://www.php.net/manual/en/function.password-hash.php
			'password' => [
				'min' => 8,
				'max' => 72,
			],
		]);

		// Check password validity
		//	Must have at least 1 lower and 1 upper characters, 1 number and 1 special character
		$password = $this->input->get('password');
		if (!\preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).*/', $password)) {
			return $this->json([
				'error' => 'Invalid password. It must contains at least 1 lowercase character, 1 uppercase character, 1 number and 1 special character.',
			], 400);
		}

		// Check duplicates
		$username = $this->input->get('username');
		$usernameTaken = User::first(['username' => $username]);
		if ($usernameTaken !== false) {
			return $this->json(['error' => 'Username taken !'], 400);
		}
		$email = $this->input->get('email');
		$emailTaken = User::first(['email' => $email]);
		if ($emailTaken !== false) {
			return $this->json(['error' => 'Email taken !'], 400);
		}

		// Hash password
		$password = \password_hash($password, \PASSWORD_BCRYPT);

		// Create the User
		$user = new User([
			'username' => $username,
			'email' => $email,
			'password' => $password,
		]);
		$user->persist();

		// Register a new valid session
		$userSession = new UserSession([
			'user' => $user->id,
			'session' => session_id(),
		]);
		$userSession->persist();

		return $this->json(['success' => 'Registered !']);
	}

	public function login()
	{
		// Validate Form Data
		$this->validate([
			'username' => [
				'min' => 4,
				'max' => 100,
			],
			'password' => [
				'min' => 8,
				'max' => 72,
			],
		]);

		// Find User
		$username = $this->input->get('username');
		$user = User::first(['username' => $username]);
		if ($user !== false) {
			$password = $this->input->get('password');
			if (!\password_verify($password, $user->password)) {
				return $this->json(['error' => 'Invalid credentials.'], 400);
			}
			if (\password_needs_rehash($user->password, \PASSWORD_BCRYPT)) {
				$user->password = \password_hash($password, \PASSWORD_BCRYPT);
				$user->persist();
			}
		} else {
			return $this->json(['error' => 'Invalid credentials.'], 400);
		}

		// Register the new valid session
		$session = \session_id();
		$userSession = UserSession::first(['user' => $user->id, 'session' => $session]);
		if (UserSession::first(['user' => $user->id, 'session' => $session]) === false) {
			$userSession = new UserSession([
				'user' => $user->id,
				'session' => $session,
			]);
			$userSession->persist();
		}

		return $this->json(['success' => 'Logged in !']);
	}

	public function logout($session = null)
	{
		if ($session === null) {
			$session = \session_id();
		}
		$userSession = UserSession::first(['user' => $this->user->id, 'session' => $session]);
		if ($userSession !== false) {
			$userSession->delete();
		}

		return $this->json(['success' => 'Logged out, see you soon !']);
	}

	public function logoutAll()
	{
		$session = \session_id();
		$userSessions = UserSession::all(['user' => $this->user->id, ['session', Operator::DIFFERENT, $session]]);
		foreach ($userSessions as $userSession) {
			$userSession->delete();
		}

		return $this->json(['success' => 'Logged out on all other sessions !']);
	}
}
