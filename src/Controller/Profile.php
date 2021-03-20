<?php namespace Controller;

use Camagru\Controller;

class Profile extends Controller
{
	public function update()
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
				'min' => 8,
				'max' => 72,
				'requiredIf' => 'newPassword',
				'match' => $passwordMatch,
			],
			'newPassword' => [
				'name' => 'new password',
				'min' => 8,
				'max' => 72,
				'requiredIf' => 'password',
				'match' => $passwordMatch,
			],
			'theme' => [
				'optional' => true,
				'match' => [
					'/light|dark/',
					'Theme must be `light` or `dark`.',
				],
			],
			'receiveComments' => [
				'optional' => true,
			],
		]);
		return $this->json(['success' => 'Profile updated !']);
	}
}
