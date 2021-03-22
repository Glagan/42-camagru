<?php namespace Exception;

use Camagru\Http\JSONResponse;
use Camagru\Http\Response;

class JSONException extends \Exception implements HTTPException
{
	/**
	 * @var int
	 */
	protected $errorCode;
	/**
	 * @var string
	 */
	protected $reason;

	public function __construct(int $errorCode)
	{
		$this->errorCode = $errorCode;
		// @see https://www.php.net/manual/en/function.json-last-error-msg.php#117393
		if ($errorCode == \JSON_ERROR_DEPTH) {
			$this->reason = 'Maximum stack depth exceeded.';
		} else if ($errorCode == \JSON_ERROR_STATE_MISMATCH) {
			$this->reason = 'Invalid or malformed JSON.';
		} else if ($errorCode == \JSON_ERROR_CTRL_CHAR) {
			$this->reason = 'Unexpected control character found.';
		} else if ($errorCode == \JSON_ERROR_SYNTAX) {
			$this->reason = 'Syntax error, malformed JSON.';
		} else if ($errorCode == \JSON_ERROR_UTF8) {
			$this->reason = 'Malformed UTF-8 characters.';
		} else {
			$this->reason = 'Unknown JSON error.';
		}
	}

	/**
	 * @param string $mode
	 * @return \Camagru\Http\Response
	 */
	public function getResponse(string $mode): Response
	{
		return new JSONResponse(['error' => $this->reason], Response::BAD_REQUEST);
	}
}
