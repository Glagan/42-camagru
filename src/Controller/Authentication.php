<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Camagru\Mail;
use Env;
use Models\User;
use Models\UserSession;
use Models\UserToken;
use SQL\Operator;
use SQL\Value;

class Authentication extends Controller
{
	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function register(): JSONResponse
	{
		$this->validate([
			'username' => [
				'min' => 4,
				'max' => 100,
			],
			'email' => [
				'validate' => \FILTER_VALIDATE_EMAIL,
			],
			// Must have at least 1 lower and 1 upper characters, 1 number and 1 special character
			'password' => [
				'min' => 8,
				// @see https://www.php.net/manual/en/function.password-hash.php
				'max' => 72,
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

		// Check and Hash password
		$password = \password_hash($this->input->get('password'), \PASSWORD_BCRYPT);

		// Create the User
		$user = new User([
			'username' => $username,
			'email' => $email,
			'verified' => false,
			'receiveComments' => true,
			'password' => $password,
		]);
		$user->persist();

		// Register a new valid session
		$session = session_id();
		$userSession = new UserSession([
			'user' => $user->id,
			'session' => $session,
			'issued' => new \DateTime(),
			'rememberMe' => false,
		]);
		$userSession->persist();
		$userSession->setCookie();

		// Generate a token for the email verification link
		$token = UserToken::first(['user' => $user->id, 'scope' => 'verification']);
		if ($token !== false) {
			$token->remove();
		}
		$token = UserToken::generate($user->id, 'verification');
		$token->persist();

		// Send mail
		$link = Env::get('Camagru', 'url') . "/verify?code={$token->token}";
		$sendMail = Mail::send(
			$user,
			"[camagru] Verify your Account",
			[
				"Welcome to camagru !",
				"Use this link to verify your account: <a href=\"{$link}\" rel=\"noreferer noopener\">{$link}</a>.",
				"As an alternative, you can enter this code in the verification page: {$token->token}",
				"You have 24 hours to use this code until it expires.",
			]
		);
		if (!$sendMail) {
			return $this->json([
				'success' => 'Registered but failed to send an Activation code, retry while logged in.',
				'user' => $user->toArray(['id', 'username', 'email', 'verified', 'receiveComments']),
			]);
		}

		return $this->json([
			'success' => 'Registered ! A link was sent to your email to verify it. You have 24hours to validate it.',
			'user' => $user->toArray(['id', 'username', 'email', 'verified', 'receiveComments']),
		]);
	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function login(): JSONResponse
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
				return $this->json(['error' => 'Invalid credentials.'], Response::UNAUTHORIZED);
			}
			if (\password_needs_rehash($user->password, \PASSWORD_BCRYPT)) {
				$user->password = \password_hash($password, \PASSWORD_BCRYPT);
				$user->persist();
			}
		} else {
			return $this->json(['error' => 'Invalid credentials.'], Response::UNAUTHORIZED);
		}

		// Clean previous invalid UserSession
		$rememberMe = $this->input->get('rememberMe') != false;
		$session = \session_id();
		UserSession::delete()
			->where([
				'user' => $user->id,
				[
					[
						'issued' => [Operator::LESS_THAN, Value::make("(NOW() - INTERVAL 1 YEAR)")],
						'rememberMe' => true,
					],
					Operator::CONDITION_OR,
					'issued' => [Operator::LESS_THAN, Value::make("(NOW() - INTERVAL 1 HOUR)")],
				],
			])
			->execute();

		// Register the new valid session or refresh an old one
		$userSession = UserSession::first(['user' => $user->id, 'session' => $session]);
		if ($userSession === false) {
			$userSession = new UserSession([
				'user' => $user->id,
				'session' => $session,
				'issued' => new \DateTime(),
				'rememberMe' => $rememberMe,
			]);
			$userSession->persist();
		} else {
			$userSession->rememberMe = $rememberMe;
			$userSession->refresh();
		}
		$userSession->setCookie();

		return $this->json([
			'success' => 'Logged in !',
			'user' => $user->toArray(['id', 'username', 'email', 'verified', 'receiveComments']),
		]);
	}

	/**
	 * @param string|null $session A PHP Session unique ID
	 * @return \Camagru\Http\JSONResponse
	 */
	public function logout($session = null): JSONResponse
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
		$userSessions = UserSession::all([
			'user' => $this->user->id,
			'session' => [Operator::DIFFERENT, $session],
		]);
		foreach ($userSessions as $userSession) {
			$userSession->remove();
		}

		return $this->json(['success' => 'Logged out on all other sessions !']);
	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function status(): JSONResponse
	{
		if ($this->auth->isLoggedIn()) {
			$user = $this->auth->getUser();
			if ($user !== false) {
				return $this->json([
					'user' => $user->toArray(['id', 'username', 'email', 'verified', 'theme', 'receiveComments']),
				]);
			}
		}
		return $this->json(['error' => 'Logged out.'], Response::UNAUTHORIZED);
	}
}
