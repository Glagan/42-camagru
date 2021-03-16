<?php namespace Controller;

use Camagru\Controller;
use Models\User;

class Authentification extends Controller
{
	public function register()
	{
		// Validate User Data
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
		$usernameTaken = User::where([['username', $username]]);
		if (\count($usernameTaken) > 0) {
			return $this->json(['error' => 'Username taken !'], 400);
		}
		$email = $this->input->get('email');
		$emailTaken = User::where([['email', $email]]);
		if (\count($emailTaken) > 0) {
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
		return $this->json(['success' => 'Registered !']);
	}

	public function login()
	{
		return $this->json(['success' => 'Logged in !']);
	}
}
