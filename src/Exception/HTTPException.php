<?php namespace Exception;

use Camagru\Http\Response;

interface HTTPException
{
	/**
	 * @param string $mode
	 * @return \Camagru\Http\Response
	 */
	public function getResponse(string $mode): Response;
}
