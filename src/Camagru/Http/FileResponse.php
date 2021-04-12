<?php namespace Camagru\Http;

class FileResponse extends Response
{
	/**
	 * @var string
	 */
	private $path;

	public function __construct(string $path, int $code = Response::OK, array $headers = [])
	{
		$this->code = $code;
		$this->headers = new HeaderList($headers);
		$this->loadFile($path);
	}

	/**
	 * Load the file, find it's mime type and add it on the Content-Type Header.
	 * @param string $path
	 * @return self
	 */
	public function loadFile($path): self
	{
		if (!\file_exists($path)) {
			return $this;
		}
		if (\is_dir($path)) {
			return $this;
		}
		$mimeType = \mime_content_type($path);
		if ($mimeType === false) {
			return $this;
		}
		$content = \file_get_contents($path);
		$this->setContent($content);
		$this->headers->add(Header::CONTENT_TYPE, $mimeType);
		return $this;

	}
}
