<?php namespace Controller;

use Camagru\Controller;

class Authentification extends Controller
{
	public function register()
	{
		return $this->json(['success' => 'Registered !']);
	}

	public function login()
	{
		return $this->json(['success' => 'Logged in !']);
	}
}
