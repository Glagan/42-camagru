<?php namespace Exception;

use Camagru\Http\Response;

interface HTTPException
{
	public function getResponse(string $mode): Response;
}
