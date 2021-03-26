<?php namespace Controller;

use Camagru\Controller;
use Camagru\Http\Response;
use Env;
use Models\Image;

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
	 * @param int $id The Image ID
	 * @return \Camagru\Http\Response
	 */
	public function single(int $id)
	{
		if ($id < 1) {
			return $this->file($this->path('400.png'), Response::BAD_REQUEST);
		}
		$image = Image::get($id);
		if ($image === false) {
			return $this->file($this->path('404.png'), Response::NOT_FOUND);
		}
		if ($image->private && (!$this->auth->isLoggedIn() || $this->user->id != $image->user)) {
			return $this->file($this->path('401.png'), Response::UNAUTHORIZED);
		}
		return $this->file($this->path($image->name));
	}
}
