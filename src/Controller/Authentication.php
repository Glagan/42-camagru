<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\Response;
use Models\User;
use Models\UserSession;
use SQL\Operator;
use SQL\Value;

class Authentication extends Controller
{
	/**
	 * @return \Camagru\Http\Response
	 */
	public function register(): Response
	{
		$this->validate([
			'username' => [
				'min' => 4,
				'max' => 100,
			],
			'email' => [
				'validate' => \FILTER_VALIDATE_EMAIL,
			],
			'password' => [
				'min' => 8,
				// @see https://www.php.net/manual/en/function.password-hash.php
				'max' => 72,
				// Must have at least 1 lower and 1 upper characters, 1 number and 1 special character
				'match' => [
					'/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).*/',
					'Invalid password. It must contains at least 1 lowercase character, 1 uppercase character, 1 number and 1 special character.',
				],
			],
		]);

		// Check duplicates
		$username = $this->input->get('username');
		$usernameTaken = User::first(['username' => $username]);
		if ($usernameTaken !== false) {
			return $this->json(['error' => 'Username taken !'], Response::BAD_REQUEST);
		}
		$email = $this->input->get('email');
		$emailTaken = User::first(['email' => $email]);
		if ($emailTaken !== false) {
			return $this->json(['error' => 'Email taken !'], Response::BAD_REQUEST);
		}

		// Hash password
		$password = \password_hash($this->input->get('password'), \PASSWORD_BCRYPT);

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

	/**
	 * @return \Camagru\Http\Response
	 */
	public function login(): Response
	{
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
				return $this->json(['error' => 'Invalid credentials.'], Response::BAD_REQUEST);
			}
			if (\password_needs_rehash($user->password, \PASSWORD_BCRYPT)) {
				$user->password = \password_hash($password, \PASSWORD_BCRYPT);
				$user->persist();
			}
		} else {
			return $this->json(['error' => 'Invalid credentials.'], Response::BAD_REQUEST);
		}

		// Clean previous invalid UserSession
		$session = \session_id();
		UserSession::delete()->where([
			'user' => $user->id,
			['issued', Operator::MORE_THAN, Value::make("(NOW() - INTERVAL 7 DAY)", true)],
		])->execute();

		// Register the new valid session or refresh the old one
		$userSession = UserSession::first(['user' => $user->id, 'session' => $session]);
		if ($userSession === false) {
			$userSession = new UserSession([
				'user' => $user->id,
				'session' => $session,
			]);
		} else {
			$userSession->issued = new \DateTime();
		}
		$userSession->persist();

		return $this->json(['success' => 'Logged in !']);
	}

	/**
	 * @param string|null $session A PHP Session unique ID
	 * @return \Camagru\Http\Response
	 */
	public function logout($session = null): Response
	{
		if ($session === null) {
			$session = \session_id();
		}
		UserSession::delete()->where([
			'session' => $session,
		])->execute();

		return $this->json(['success' => 'Logged out, see you soon !']);
	}

	/**
	 * @return \Camagru\Http\Response
	 */
	public function logoutAll(): Response
	{
		$session = \session_id();
		$userSessions = UserSession::all(['user' => $this->user->id, ['session', Operator::DIFFERENT, $session]]);
		foreach ($userSessions as $userSession) {
			$userSession->remove();
		}

		return $this->json(['success' => 'Logged out on all other sessions !']);
	}

	/**
	 * @return \Camagru\Http\Response
	 */
	public function sendVerification(): Response
	{
		// TODO
		return $this->json(['success' => 'An email with a new verification link has been sent.']);
	}

	/**
	 * @return \Camagru\Http\Response
	 */
	public function resetPassword(): Response
	{
		$this->validate([
			'username' => [
				'min' => 4,
				'max' => 100,
			],
		]);
		// TODO
		return $this->json(['success' => 'An email with a link to reset your password has been sent.']);
	}
}
