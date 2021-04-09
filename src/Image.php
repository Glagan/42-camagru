<?php

class Image
{
	private $resource;

	public function __construct()
	{
		$this->resource = null;
	}

	/**
	 * @param string $str
	 * @return \Image|false
	 */
	public static function fromString(string $str)
	{
		$image = new Image();
		$result = $image->load($str);
		return $result;
	}

	/**
	 * Wrapper from \imagecreatefromstring with added save alpha and alpha blending activated.
	 * @param string $str
	 * @return self|false
	 */
	public function load(string $str)
	{
		$tmp = \imagecreatefromstring($str);
		if ($tmp !== false) {
			\imagealphablending($tmp, true);
			\imagesavealpha($tmp, true);
			$this->resource = $tmp;
			return $this;
		}
		return false;
	}

	/**
	 * @param integer $width
	 * @param integer $height
	 * @return \Image
	 */
	public static function create(int $width, int $height)
	{
		$image = new Image();
		$image->canvas($width, $height);
		return $image;
	}

	/**
	 * Wrapper for \imagecreatetruecolor with added save alpha and alpha blending activated
	 * @param integer $width
	 * @param integer $height
	 * @return \Image
	 */
	public function canvas(int $width, int $height): void
	{
		$tmp = \imagecreatetruecolor($width, $height);
		\imagefill($tmp, 0, 0, \imagecolorallocatealpha($tmp, 0, 0, 0, 127));
		\imagealphablending($tmp, true);
		\imagesavealpha($tmp, true);
		$this->resource = $tmp;
	}

	public function width(): int
	{
		return \imagesx($this->resource);
	}

	public function height(): int
	{
		return \imagesy($this->resource);
	}

	/**
	 * @see https://www.php.net/manual/en/function.imagecopymerge.php#92787
	 **/
	public function merge(\Image $layer, int $x, int $y): void
	{
		$cut = Image::create($layer->width(), $layer->height());
		\imagecopy($cut->resource, $this->resource, 0, 0, $x, $y, $layer->width(), $layer->height());
		\imagecopy($cut->resource, $layer->resource, 0, 0, 0, 0, $layer->width(), $layer->height());
		\imagecopymerge($this->resource, $cut->resource, $x, $y, 0, 0, $layer->width(), $layer->height(), 100);
	}

	public function resize(int $width, int $height): void
	{
		$tmp = Image::create($width, $height);
		\imagecopyresampled($tmp->resource, $this->resource, 0, 0, 0, 0, $width, $height, $this->width(), $this->height());
		\imagedestroy($this->resource);
		$this->resource = $tmp->resource;
		$tmp->resource = null; // Avoid resource reference being destroyed
	}

	public function save(string $path): bool
	{
		return \imagepng($this->resource, $path);
	}

	public function __destruct()
	{
		if ($this->resource != null) {
			\imagedestroy($this->resource);
			$this->resource = null;
		}
	}
}
