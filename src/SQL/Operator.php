<?php namespace SQL;

class Operator
{
	const EQUAL = '=';
	const DIFFERENT = '!=';
	const LESS_THAN = '<';
	const LESS_OR_EQUAL = '<=';
	const MORE_THAN = '>';
	const MORE_OR_EQUAL = '>=';
	const IN = 'IN';
	const NOT_IN = 'NOT IN';
	const IS_NULL = 'IN';
	const IS_NOT_NULL = 'IN';
	// const LIKE = 'LIKE';
	// const NOT_LIKE = 'NOT LIKE';

	const CONDITION_AND = 'AND';
	const CONDITION_OR = 'OR';

	const VALID_OPERATORS = [
		self::EQUAL,
		self::DIFFERENT,
		self::LESS_THAN,
		self::LESS_OR_EQUAL,
		self::MORE_THAN,
		self::MORE_OR_EQUAL,
		self::IN,
		self::NOT_IN,
		self::IS_NULL,
		self::IS_NOT_NULL,
		self::CONDITION_AND,
		self::CONDITION_OR,
	];

	public static function isOperator(string $value): bool
	{
		return \in_array($value, self::VALID_OPERATORS);
	}
}
