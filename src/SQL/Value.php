<?php namespace SQL;

class Value
{
	/**
	 * @var string
	 */
	private $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	/**
	 * Get the raw value.
	 * @return string
	 */
	public function get(): string
	{
		return $this->value;
	}

	/**
	 * Helper function to create a new Value instance.
	 * @param string $value
	 * @return Value
	 */
	public static function make(string $value): Value
	{
		return new Value($value);
	}
}
