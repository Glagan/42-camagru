<?php namespace Controller;

use Camagru\Controller;

class Status extends Controller
{
	public function status()
	{
		return $this->json(['ping' => 'pong']);
	}
}
