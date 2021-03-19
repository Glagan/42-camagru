<?php namespace SQL;

class Condition
{
	private $column;
	private $operator;
	private $value;

	public function __construct(string $column, string $operator, $value = null)
	{
		$this->column = $column;
		$this->operator = $operator;
		$this->value = $value;
	}

	/**
	 * Build the SQL WHERE clause
	 * @return string
	 */
	public function build(): string
	{
		$clause = "{$this->column} {$this->operator}";
		if ($this->operator == Operator::IN || $this->operator == Operator::NOT_IN) {
			$length = \is_array($this->value) ? \count($this->value) : 1;
			$placeholders = \implode(', ', \array_fill(0, $length, '?'));
			$clause .= " ({$placeholders})";
		} else if ($this->operator != Operator::IS_NULL && $this->operator != Operator::IS_NOT_NULL) {
			$clause .= " ?";
		}
		return $clause;
	}

	/**
	 * Return all values if there is placeholders.
	 * @return array
	 */
	public function values(): array
	{
		if ($this->operator == Operator::IS_NULL || $this->operator == Operator::IS_NOT_NULL) {
			return [];
		}
		if (\is_array($this->value)) {
			return $this->value;
		}
		return [$this->value];
	}
}
