<?php namespace Camagru\Http;

class NotFound extends Response
{
	public function __construct()
	{
		$this->code = Response::NOT_FOUND;
		$this->body = ['error' => 'Not found !'];
	}
}
