<?php namespace Value;

use Exception\ValueException;

class Value
{
	private $value;
	private $isRaw;

	public function __construct($value, $isRaw = false)
	{
		$this->value = $value;
		$this->isRaw = $isRaw;
	}

	public function placeholder()
	{
		if ($this->isRaw) {
			return $this->value;
		}
		return '?';
	}

	public function value()
	{
		return $this->value;
	}

	/**
	 * INTERVAL
	 */

	const VALID_INTERVAL_UNITS =
		['SECOND',
		'SECOND',
		'MINUTE',
		'HOUR',
		'DAY',
		'WEEK',
		'MONTH',
		'QUARTER',
		'YEAR'];

	/**
	 * @see https://dev.mysql.com/doc/refman/8.0/en/expressions.html#temporal-intervals
	 * @param mixed $unit
	 * @param mixed $expression
	 * @return void
	 */
	public static function interval($unit, $expression)
	{
		if (!\in_array($unit, self::VALID_INTERVAL_UNITS) || !\is_int($expression)) {
			throw new ValueException("Invalid INTERVAL unit ($unit) or expression ($expression).");
		}
		return new Value("INTERVAL $expression $unit", true);
	}
}
