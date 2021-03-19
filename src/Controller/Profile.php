<?php namespace Controller;

use Camagru\Controller;

class Profile extends Controller
{
	public function single()
	{
		return $this->json([
			'user' => $this->user->toArray(['id', 'username', 'verified']),
		]);
	}
}
