<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Camagru\Mail;
use Env;
use Models\Creation;
use Models\User;
use Models\UserSession;
use Models\UserToken;

class Account extends Controller
{
	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function sendResetPassword(): JSONResponse
	{
		$this->validate([
			'email' => [
				'type' => 'string',
				'validate' => \FILTER_VALIDATE_EMAIL,
			],
		]);

		// Check if the email exists
		$email = $this->input->get('email');
		$user = User::first(['email' => $email]);
		if ($user === false) {
			return $this->json(['error' => 'This email doesn\'t belong to anyone.'], Response::BAD_REQUEST);
		}

		// Generate a token that will be used when resetting the password
		$token = UserToken::first(['user' => $user->id, 'scope' => 'password']);
		if ($token !== false) {
			$diff = $token->issued->diff(new \DateTime(), true);
			// Remove old invalid token if it was more than 1 day ago
			if ($diff->days > 1) {
				$token->remove();
			}
			// Abort if the last code was sent less than 10 minutes ago
			else if ($diff->i < 10) {
				return $this->json(['error' => 'Wait a bit before asking for another code.'], Response::BAD_REQUEST);
			}
		}
		$token = UserToken::generate($user->id, 'password');
		$token->persist();

		// Send the mail
		$link = Env::get('Camagru', 'url') . "/reset-password?code={$token->token}";
		$sendMail = Mail::send(
			$user,
			"[camagru] Password reset",
			[
				"You requested to reset your password for you account {$user->username}.",
				"Use this link to set a new password: <a href=\"{$link}\" rel=\"noreferer noopener\">{$link}</a>.",
				"As an alternative, you can enter this code in the Reset Passwrod page: {$token->token}",
				"You have 24 hours to use this code until it expires.",
			]
		);
		if (!$sendMail) {
			return $this->json([
				'error' => 'Failed to send the email.',
			]);
		}

		return $this->json([
			'success' => 'An email with a link to reset your password has been sent. You have 24hours to reset your password.',
		]);
	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function resetPassword(): JSONResponse
	{
		$this->validate([
			'code' => [
				'type' => 'string',
				'min' => 50, 'max' => 50,
			],
			'password' => [
				'type' => 'string',
				'name' => 'new password',
				'min' => 8,
				'max' => 72,
				'match' => [
					'/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).*/',
					'Invalid password. It must contains at least 1 lowercase character, 1 uppercase character, 1 number and 1 special character.',
				],
			],
		]);

		$code = $this->input->get('code');
		$token = UserToken::first(['token' => $code, 'scope' => 'password']);
		if ($token === false) {
			return $this->json(['error' => 'No account found for this code.'], Response::BAD_REQUEST);
		}
		$now = new \DateTime();
		$diff = $now->diff($token->issued, true);
		if ($diff->days >= 1) {
			$token->remove();
			return $this->json(['error' => 'Code expired, ask for a new one.'], Response::BAD_REQUEST);
		}

		$user = User::get($token->user);
		$user->password = \password_hash($this->input->get('password'), \PASSWORD_BCRYPT);
		$user->persist();
		$token->remove();
		return $this->json(['success' => 'Password resetted, you can now login.']);
	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function sendVerification(): JSONResponse
	{
		if ($this->user->verified) {
			return $this->json(['error' => 'You are already verified.'], Response::BAD_REQUEST);
		}

		// Generate a token that will be used in the verification link
		$token = UserToken::first(['user' => $this->user->id, 'scope' => 'verification']);
		if ($token !== false) {
			$diff = $token->issued->diff(new \DateTime(), true);
			// Remove old invalid token if it was more than 1 day ago
			if ($diff->days > 1) {
				$token->remove();
			}
			// Abort if the last code was sent less than 10 minutes ago
			else if ($diff->i < 10) {
				return $this->json(['error' => 'Wait a bit before asking for another code.'], Response::BAD_REQUEST);
			}
		}
		$token = UserToken::generate($this->user->id, 'verification');
		$token->persist();

		// Send the mail
		$link = Env::get('Camagru', 'url') . "/verify?code={$token->token}";
		$sendMail = Mail::send(
			$this->user,
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
				'error' => 'Failed to send the email.',
			]);
		}

		return $this->json([
			'success' => 'An email with a new verification link has been sent. You have 24hours to activate it.',
		]);

	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function verify(): JSONResponse
	{
		$this->validate([
			'code' => [
				'type' => 'string',
				'min' => 50, 'max' => 50,
			],
		]);

		if ($this->user->verified) {
			return $this->json(['error' => 'Account already verified.'], Response::BAD_REQUEST);
		}

		$token = UserToken::first(['user' => $this->user->id, 'scope' => 'verification']);
		if ($token === false) {
			return $this->json(['error' => 'No Activation code found, ask for a new one.'], Response::BAD_REQUEST);
		}
		$now = new \DateTime();
		$diff = $now->diff($token->issued, true);
		if ($token->token !== $this->input->get('code')) {
			return $this->json(['error' => 'Invalid Activation code.'], Response::BAD_REQUEST);
		}
		$token->remove();
		if ($diff->days >= 1) {
			return $this->json(['error' => 'Code expired, ask for a new one.'], Response::BAD_REQUEST);
		}

		$this->user->verified = true;
		$this->user->persist();
		return $this->json(['success' => 'Account verified !']);
	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function update(): JSONResponse
	{
		$passwordMatch = [
			'/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).*/',
			'Invalid password. It must contains at least 1 lowercase character, 1 uppercase character, 1 number and 1 special character.',
		];
		$this->validate([
			'username' => [
				'type' => 'string',
				'optional' => true,
				'min' => 4,
				'max' => 100,
			],
			'email' => [
				'type' => 'string',
				'optional' => true,
				'validate' => \FILTER_VALIDATE_EMAIL,
				'min' => 4,
				'max' => 100,
			],
			'password' => [
				'type' => 'string',
				'name' => 'new password',
				'min' => 8,
				'max' => 72,
				'optional' => true,
				'match' => $passwordMatch,
			],
			'receiveComments' => [
				'optional' => true,
			],
			'currentPassword' => [
				'type' => 'string',
				'min' => 8,
				'max' => 72,
				'match' => $passwordMatch,
			],
		]);

		// Check password first
		$currentPassword = $this->input->get('currentPassword');
		if ($currentPassword === false) {
			return $this->json(['error' => 'You need your current password to update your profile !'], Response::UNAUTHORIZED);
		}
		if (!\password_verify($currentPassword, $this->user->password)) {
			return $this->json(['error' => 'Invalid credentials.'], Response::UNAUTHORIZED);
		}

		// Username, check if it's present and not already taken by *another* user
		$username = $this->input->get('username');
		if ($username !== false && $username != $this->user->username) {
			$exists = User::first(['username' => $username]);
			if ($exists !== false) {
				return $this->json(['error' => 'Username taken !'], Response::BAD_REQUEST);
			}
			$this->user->username = $username;
		}

		// Email, if updated reset verified to false
		$email = $this->input->get('email');
		$updatedMail = false;
		if ($email !== false && $email != $this->user->email) {
			$exists = User::first(['email' => $email]);
			if ($exists !== false) {
				return $this->json(['error' => 'Email taken !'], Response::BAD_REQUEST);
			}
			$this->user->email = $email;
			$this->user->verified = false;
			$updatedMail = true;
		}

		// Password
		$password = $this->input->get('password');
		if ($password !== false && !\password_verify($password, $this->user->password)) {
			$this->user->password = \password_hash($password, \PASSWORD_BCRYPT);
		}

		// Save
		$this->user->receiveComments = $this->input->get('receiveComments', false) == true;
		$this->user->persist();

		// Send mail if needed
		if ($updatedMail) {
			// Generate a token for the email verification link
			$token = UserToken::first(['user' => $this->user->id, 'scope' => 'verification']);
			if ($token !== false) {
				$token->remove();
			}
			$token = UserToken::generate($this->user->id, 'verification');
			$token->persist();

			// Send mail
			$link = Env::get('Camagru', 'url') . "/verify?code={$token->token}";
			$sendMail = Mail::send(
				$this->user,
				"[camagru] Email changed",
				[
					"You changed your email on camagru.",
					"Use this link to verify your account again: <a href=\"{$link}\" rel=\"noreferer noopener\">{$link}</a>.",
					"As an alternative, you can enter this code in the verification page: {$token->token}",
					"You have 24 hours to use this code until it expires.",
				]
			);
			if (!$sendMail) {
				return $this->json([
					'success' => 'Profile updated but failed to send an Activation code, retry while logged in.',
					'verified' => $this->user->verified,
				]);
			}
			return $this->json([
				'success' => 'Profile updated ! A link was sent to your new email to verify it. You have 24hours to validate it.',
				'verified' => $this->user->verified,
			]);
		}
		return $this->json(['success' => 'Profile updated !', 'verified' => $this->user->verified]);
	}

	/**
	 * @return \Camagru\Http\JSONResponse
	 */
	public function deleteAccount(): JSONResponse
	{
		$this->validate([
			'password' => [
				'type' => 'string',
				'min' => 8,
				'max' => 72,
				'match' => [
					'/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).*/',
					'Invalid password. It must contains at least 1 lowercase character, 1 uppercase character, 1 number and 1 special character.',
				],
			],
		]);

		// Check password
		$password = $this->input->get('password');
		if ($password === false) {
			return $this->json(['error' => 'You need your current password to delete your profile !'], Response::UNAUTHORIZED);
		}
		if (!\password_verify($password, $this->user->password)) {
			return $this->json(['error' => 'Invalid credentials.'], Response::UNAUTHORIZED);
		}

		$creations = Creation::select()
			->columns(['name'])
			->where(['user' => $this->user->id])
			->all();

		foreach ($creations as $creation) {
			unlink(Env::get('Camagru', 'uploads') . "/{$creation['name']}");
		}

		$session = \session_id();
		UserSession::delete()->where([
			'session' => $session,
		])->execute();

		$this->user->delete()->where(['id' => $this->user->id])->execute();
		return $this->json(['success' => 'Account deleted !']);
	}
}
