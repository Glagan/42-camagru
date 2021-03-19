<?php namespace SQL;

class Value
{
	private $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	public function get()
	{
		return $this->value;
	}

	public static function make(string $value)
	{
		return new Value($value);
	}
}
