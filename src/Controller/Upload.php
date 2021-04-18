<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\FileResponse;
use Camagru\Http\Response;
use Env;
use Models\Creation;

class Upload extends Controller
{
	/**
	 * Retrieve the complete path to a file in the uploads folder.
	 * @param string $filename
	 * @return string
	 */
	private function path(string $filename): string
	{
		return Env::get('Camagru', 'root') . '/storage/uploads/' . $filename;
	}

	/**
	 * @param int $id The Creation ID
	 * @return \Camagru\Http\FileResponse
	 */
	public function single(int $id): FileResponse
	{
		if ($id < 1) {
			return $this->file($this->path('400.png'), Response::BAD_REQUEST);
		}
		$image = Creation::get($id);
		if ($image === false) {
			return $this->file($this->path('404.png'), Response::NOT_FOUND);
		}
		return $this->file($this->path($image->name));
	}
}
