<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\Response;
use Models\Image;
use Models\User;

class Profile extends Controller
{
	/**
	 * @return \Camagru\Http\Response
	 */
	public function update(): Response
	{
		$passwordMatch = [
			'/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).*/',
			'Invalid password. It must contains at least 1 lowercase character, 1 uppercase character, 1 number and 1 special character.',
		];
		$this->validate([
			'username' => [
				'optional' => true,
				'min' => 4,
				'max' => 100,
			],
			'email' => [
				'optional' => true,
				'validate' => \FILTER_VALIDATE_EMAIL,
				'min' => 4,
				'max' => 100,
			],
			'password' => [
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
		if ($email !== false && $email != $this->user->email) {
			$exists = User::first(['email' => $email]);
			if ($exists !== false) {
				return $this->json(['error' => 'Email taken !'], Response::BAD_REQUEST);
			}
			$this->user->email = $email;
			$this->user->verified = false;
		}

		// Password
		$password = $this->input->get('password');
		if ($password !== false && !\password_verify($password, $this->user->password)) {
			$this->user->password = \password_hash($password, \PASSWORD_BCRYPT);
		}

		$this->user->persist();
		return $this->json(['success' => 'Profile updated !', 'verified' => $this->user->verified]);
	}

	/**
	 * @param int $id User ID
	 * @return \Camagru\Http\Response
	 */
	public function single($id): Response
	{
		if ($id < 1) {
			return $this->json(['error' => 'Invalid Image ID.'], Response::BAD_REQUEST);
		}

		// User
		$user = User::get($id);
		if ($user === false) {
			return $this->json(['error' => 'User not found.'], Response::NOT_FOUND);
		}
		$private = $this->auth->isLoggedIn() && $this->user->id == $id;

		// Images
		$images = Image::all(['user' => $user->id, 'private' => $private]);
		$foundImages = [];
		foreach ($images as $image) {
			$foundImages[] = $image->toArray(['id', 'user', 'name', 'at']);
		}

		return $this->json([
			'user' => $user->toArray(['id', 'username', 'verified']),
			'images' => $foundImages,
		]);
	}
}
