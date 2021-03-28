<?php namespace SQL;

use Database;
use Exception\QueryException;
use Exception\SQLException;
use Log;

class Query
{
	public const SELECT = "SELECT";
	public const INSERT = "INSERT";
	public const UPDATE = "UPDATE";
	public const DELETE = "DELETE";

	public const ASC = "ASC";
	public const DESC = "DESC";

	/**
	 * @var string
	 */
	private $type;
	/**
	 * @var array
	 */
	private $table;
	/**
	 * @var array
	 */
	private $fields;
	/**
	 * @var array
	 */
	private $updates;
	/**
	 * @var array
	 */
	private $inserts;
	/**
	 * @var array
	 */
	private $conditions;
	/**
	 * @var array
	 */
	private $order;
	/**
	 * @var int
	 */
	private $limit;
	/**
	 * @var int
	 */
	private $offset;
	/**
	 * @var array
	 */
	private $params;
	/**
	 * @var \PDOStatement
	 */
	private $statement;
	/**
	 * @var mixed
	 */
	private $result;

	/**
	 * @param string $type
	 * @param string|array $table
	 */
	public function __construct(string $type, $table)
	{
		$this->setType($type)
			->setTable($table);
		$this->fields = [];
		$this->updates = [];
		$this->inserts = [];
		$this->conditions = [];
		$this->order = [];
		$this->limit = -1;
		$this->offset = -1;
		$this->params = [];
	}

	/**
	 * @param string $type
	 * @return self
	 */
	public function setType(string $type): self
	{
		if ($type == self::SELECT ||
			$type == self::INSERT ||
			$type == self::UPDATE ||
			$type == self::DELETE) {
			$this->type = $type;
		} else {
			throw new QueryException($this, 'Invalid Query type.');
		}
		return $this;
	}

	/**
	 * Set the table(s) on which the query is applied.
	 * Format:
	 * 	A simple string for a simple table name
	 * 	Array ['name'] or ['name' => 'alias']
	 * @param string|array $table
	 * @return self
	 */
	public function setTable($table): self
	{
		$this->table = [];
		if (\is_array($table)) {
			foreach ($table as $key => $value) {
				if (\is_string($value[$key])) {
					$this->table[] = ['name' => $value, 'alias' => $key];
				} else {
					$this->table[] = ['name' => $value];
				}
			}
		} else {
			$this->table[] = ['name' => $table];
		}
		return $this;
	}

	/**
	 * Set the fields retrieved from the query.
	 * Format:
	 * 	An array of ['field1', 'field2', 'field3' => 'alias']
	 * @param array $fields
	 * @return self
	 */
	public function columns(array $fields): self
	{
		$this->fields = [];
		foreach ($fields as $key => $value) {
			if (\is_string($key)) {
				$this->fields[] = ['column' => $value, 'alias' => $key];
			} else {
				$this->fields[] = ['column' => $value];
			}
		}
		return $this;
	}

	/**
	 * Set the fields that will be updated or inserted in an UPDATE or INSERT query.
	 * Format:
	 * 	['column' => 'value']
	 * @param array $updates
	 * @return self
	 */
	public function set(array $values): self
	{
		if ($this->type == Query::INSERT) {
			$this->inserts = [];
			if (\count($values) > 0 && \is_array($values[\array_key_first($values)])) {
				foreach ($values as $group) {
					$this->inserts[] = $group;
				}
			} else {
				$this->inserts[] = $values;
			}
		} else {
			$this->updates = $values;
		}
		return $this;
	}

	/**
	 * Set the WHERE conditions of a query.
	 * Format:
	 * 	['field' => 'value', ['field', 'operator', 'value'], ['field', 'value']]
	 * 	If the condition is not an array or if the condition is an array without an operator, EQUAL is assumed.
	 * @param array $conditions
	 * @return self
	 */
	public function where(array $conditions): self
	{
		$this->conditions = [];
		foreach ($conditions as $key => $value) {
			if (\is_string($key)) {
				// Consider an array value as an IN condition
				if (\is_array($value)) {
					$this->conditions[] = ['column' => $key, 'operator' => Operator::IN, 'value' => $value];
				} else {
					$this->conditions[] = ['column' => $key, 'operator' => Operator::EQUAL, 'value' => $value];
				}
			} else {
				// Check if there is 2 or 3 arguments
				// The operator is in the middle if there is 3 arguments
				// Check if the value is an array for an IN condition if there is only 2 arguments
				if (\is_array($value)) {
					$hasOperator = \count($value) == 3;
					$operator = $hasOperator ? $value[1] : Operator::EQUAL;
					$conditionValue = $hasOperator ? $value[2] : $value[1];
					if (!$hasOperator && \is_array($conditionValue)) {
						$operator = Operator::IN;
					}
					$this->conditions[] = ['column' => $value[0], 'operator' => $operator, 'value' => $conditionValue];
				}
			}
		}
		return $this;
	}

	/**
	 * Set the limit and optional offset of the query.
	 * @param int $limit
	 * @param int $offset
	 * @return self
	 */
	public function limit(int $limit, int $offset = -1): self
	{
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Set the limit and offset for a given page in a pagination with perPage elements.
	 * @param int $page
	 * @param int $perPage
	 * @return self
	 */
	public function page(int $page, int $perPage): self
	{
		$this->limit = $perPage;
		$this->offset = (\max($page - 1, 0) * $perPage);
		return $this;
	}

	/**
	 * Set the column which the query is ordered by and the direction.
	 * $direction is only used if $order is a string.
	 * Format:
	 * 	['column'] with a default ASC direction
	 * 	['column' => 'direction']
	 * @param string|array $order
	 * @param string $direction
	 * @return self
	 */
	public function orderBy($order, string $direction = self::ASC): self
	{
		$this->order = [];
		if (\is_array($order)) {
			foreach ($order as $key => $value) {
				if (\is_string($value[$key])) {
					$this->order[] = ['column' => $key, 'direction' => $value];
				} else if ($value == self::ASC || $value == self::DESC) {
					$this->order[] = ['column' => $value, 'direction' => self::ASC];
				}
			}
		} else if (!\is_null($order)) {
			$this->order[] = ['column' => $order, 'direction' => $direction];
		}
		return $this;
	}

	/**
	 * Build the SQL Query with placeholders and returns it.
	 * @return string
	 */
	public function build(): string
	{
		// Query statement
		$sql = '';
		if ($this->type == self::INSERT) {
			$sql = 'INSERT INTO';
		} else if ($this->type == self::DELETE) {
			$sql = 'DELETE FROM';
		} else {
			$sql = $this->type;
		}

		// Add fields
		if ($this->type == self::SELECT) {
			if (\count($this->fields) == 0) {
				$sql .= " *";
			} else {
				$sql .= \implode(',', \array_map(function ($field) {
					if ($field instanceof Value) {
						return $field->get();
					}
					if ($field['column'] instanceof Value) {
						$fieldValue = " {$field['column']->get()}";
					} else {
						$fieldValue = " {$field['column']}";
					}
					if (isset($field['alias'])) {
						$fieldValue .= " AS {$field['alias']}";
					}
					return $fieldValue;
				}, $this->fields));
			}
			$sql .= " FROM";
		}

		// Add table
		$sql .= \implode(', ', \array_map(function (array $table) {
			$ret = " {$table['name']}";
			if (isset($table['alias'])) {
				$ret .= " AS {$table['alias']}";
			}
			return $ret;
		}, $this->table));

		// INSERT has the list of columns SET as a delimiter and the values inside parenthesis
		//	A 2D array of array is expected the nested array representing a single group of values
		$this->params = [];
		if ($this->type == self::INSERT && \count($this->inserts) > 0) {
			$columnCount = -1;
			$columns = [];
			$groups = [];
			// Generate each groups of the insert
			foreach ($this->inserts as $group) {
				if (\is_array($group) && \count($group) > 0) {
					// Create a group of placeholders (or values)
					$count = 0;
					$currentGroup = [];
					foreach ($group as $value) {
						if ($value instanceof Value) {
							$currentGroup[] = $value->get();
						} else {
							$currentGroup[] = '?';
							$this->params[] = $value;
						}
						$count++;
					}
					// Count the number of columns to avoid missmatch
					if ($columnCount < 0) {
						$columnCount = $count;
						$columns = \array_keys($group);
					} else if ($columnCount != $count) {
						throw new QueryException($this, "Invalid number of columns in INSERT.");
					}
					// Add the groupe to the list
					$values = \implode(', ', $currentGroup);
					$groups[] = "({$values})";
				}
			}
			// Generate the complete INSERT
			if (\count($groups) > 0) {
				$columns = \implode(', ', $columns);
				$values = \implode(', ', $groups);
				$sql .= " ({$columns}) VALUES {$values}";
			}
		}

		// UPDATE has a SET delimiter and a list of assignments
		//	A 1D array is expected, unlike in INSERT
		if ($this->type == self::UPDATE && \count($this->updates) > 0) {
			$assignments = [];
			foreach ($this->updates as $key => $value) {
				if ($value instanceof Value) {
					$assignments[] = "{$key} = {$value->get()}";
				} else {
					$assignments[] = "{$key} = ?";
					$this->params[] = $value;
				}
			}
			$assignments = \implode(', ', $assignments);
			$sql .= " SET {$assignments}";
		}

		// Add conditions
		if ($this->type != self::INSERT && \count($this->conditions) > 0) {
			$conditions = [];
			foreach ($this->conditions as $group) {
				$operator = $group['operator'];
				if ($operator == Operator::IN || $operator == Operator::NOT_IN) {
					$length = \is_array($group['value']) ? \count($group['value']) : 1;
					$placeholders = \implode(', ', \array_fill(0, $length, '?'));
					$conditions[] = "{$group['column']} {$group['operator']} ({$placeholders})";
					\array_push($this->params, ...\array_values($group['value']));
				} else if ($operator != Operator::IS_NULL && $operator != Operator::IS_NOT_NULL) {
					if ($group['value'] instanceof Value) {
						$conditions[] = "{$group['column']} {$group['operator']} {$group['value']->get()}";
					} else {
						$conditions[] = "{$group['column']} {$group['operator']} ?";
						$this->params[] = $group['value'];
					}
				} else {
					$conditions[] = "{$group['column']} {$group['operator']}";
				}
			}
			$sql .= (' WHERE ' . \implode(' AND ', $conditions));
		}

		// Add order
		if ($this->type != self::INSERT && \count($this->order) > 0) {
			$sql .= " ORDER BY ";
			$sql .= \implode(', ', \array_map(function ($order) {
				return "{$order['column']} {$order['direction']}";
			}, $this->order));
		}

		// Add limit and offset
		if ($this->type != self::INSERT && $this->limit > 0) {
			$sql .= " LIMIT {$this->limit}";
			if ($this->offset > 0) {
				$sql .= " OFFSET {$this->offset}";
			}
		}

		return $sql;
	}

	/**
	 * Build, prepare and execute the query.
	 * Returns the result of the execution.
	 * @return bool
	 */
	public function execute(): bool
	{
		$query = $this->build();
		Log::debug('Execute ' . $query);
		$this->statement = Database::connection()->prepare($query);
		if ($this->statement === false) {
			throw new QueryException($this, 'Failed to prepare statement.');
		}
		// Bind each parameters
		$idx = 1;
		foreach ($this->params as $param) {
			// Convert value if necessary
			$value = $param;
			if ($param instanceof \DateTime) {
				$value = $param->format('Y-m-d H:i:s');
			}
			// Find PDO type
			$type = false;
			if (\is_int($value)) {
				$type = \PDO::PARAM_INT;
			} elseif (\is_bool($value)) {
				$type = \PDO::PARAM_BOOL;
			} elseif (\is_null($value)) {
				$type = \PDO::PARAM_NULL;
			} elseif (\is_string($value)) {
				$type = \PDO::PARAM_STR;
			}
			// Bind
			$this->statement->bindValue($idx++, $value, $type);
		}
		$result = $this->statement->execute();
		if ($result === false) {
			throw new SQLException($this->statement);
		}
		return true;
	}

	/**
	 * Execute the Query and return the first returned row or false if it does not exists.
	 * @param string|int|null $classOrMode A PDO mode or a Classname
	 * @return mixed
	 */
	public function first($classOrMode = null)
	{
		$this->execute();
		// $classOrMode can be a PDO flag or a Classname
		if ($classOrMode !== null) {
			if (\is_int($classOrMode)) {
				$this->result = $this->statement->fetch($classOrMode);
			} else {
				$this->statement->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $classOrMode);
				$this->result = $this->statement->fetch();
			}
		}
		// If nothing is set we use FETCH_ASSOC instead of the default FETCH_BOTH
		else {
			$this->result = $this->statement->fetch(\PDO::FETCH_ASSOC);
		}
		// PDO returns false if no rows are found and throws on failure
		// We can safely use the $result
		return $this->result;
	}

	/**
	 * Execute the Query and return all matching rows or false if it does not exists.
	 * @param string|int|null $classOrMode A PDO mode or a Classname
	 * @return mixed
	 */
	public function all($classOrMode = null)
	{
		$this->execute();
		// $classOrMode can be a PDO flag or a Classname
		if ($classOrMode !== null) {
			if (\is_int($classOrMode)) {
				$this->result = $this->statement->fetchAll($classOrMode);
			} else {
				$this->statement->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $classOrMode);
				$this->result = $this->statement->fetchAll();
			}
		}
		// If nothing is set we use FETCH_ASSOC instead of the default FETCH_BOTH
		else {
			$this->result = $this->statement->fetchAll(\PDO::FETCH_ASSOC);
		}
		// PDO returns an empty array if no rows are found and throws on failure
		return $this->result;
	}
}
