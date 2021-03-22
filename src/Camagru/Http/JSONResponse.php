<?php namespace Camagru\Http;

class JSONResponse extends Response
{
	public function __construct(array $content = [], int $code = Response::OK, array $headers = [])
	{
		$this->code = $code;
		$this->headers = new HeaderList($headers);
		$this->setContent($content);
	}

	/**
	 * Set the content of the Response.
	 * Automatically add the Content-Type Header and json_encode the body.
	 * @param array $content
	 * @return self
	 */
	public function setContent($content): self
	{
		$this->content = \json_encode($content);
		$this->headers->add(Header::CONTENT_TYPE, Header::JSON_TYPE_UTF8);
		return $this;
	}
}
