<?php namespace SQL;

use Database;
use Exception\QueryException;
use Exception\SQLException;

class Query
{
	public const SELECT = "SELECT";
	public const INSERT = "INSERT";
	public const UPDATE = "UPDATE";
	public const DELETE = "DELETE";

	public const ASC = "ASC";
	public const DESC = "DESC";

	private $type;
	private $table;
	private $fields;
	private $updates;
	private $inserts;
	private $conditions;
	private $order;
	private $limit;
	private $offset;
	private $placeholders;
	private $statement;
	private $result;

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
		$this->placeholders = [];
	}

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
				if (empty($key)) {
					$this->table[] = ['name' => $value];
				} else {
					$this->table[] = ['name' => $key, 'alias' => $value];
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
	public function select(array $fields): self
	{
		$this->fields = [];
		foreach ($fields as $key => $value) {
			if (empty($key)) {
				$this->fields[] = ['column' => $value];
			} else {
				$this->fields[] = ['column' => $key, 'alias' => $value];
			}
		}
		return $this;
	}

	/**
	 * Set the fields that will be updated in an UPDATE query.
	 * Format:
	 * 	['column' => 'value']
	 * @param array $updates
	 * @return self
	 */
	public function set(array $updates): self
	{
		$this->updates = $updates;
		return $this;
	}

	/**
	 * Set the fields that will be updated in an UPDATE query.
	 * Format:
	 * 	['column' => 'value']
	 * @param array $updates
	 * @return self
	 */
	public function insert(array $values): self
	{
		if (\count($values) > 0 && \is_array($values[\array_key_first($values)])) {
			foreach ($values as $group) {
				$this->inserts[] = $group;
			}
		} else {
			$this->inserts[] = $values;
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
	public function where($conditions): self
	{
		$this->conditions = [];
		foreach ($conditions as $key => $value) {
			if (empty($key)) {
				if (\is_array($value)) {
					if (\count($value) == 2) {
						$this->conditions[] = ['column' => $value[0], 'operator' => Operator::EQUAL, 'value' => $value[1]];
					} else {
						$this->conditions[] = ['column' => $value[0], 'operator' => $value[1], 'value' => $value[2]];
					}
				}
			} else {
				$this->conditions[] = ['column' => $key, 'operator' => Operator::EQUAL, 'value' => $value];
			}
		}
		return $this;
	}

	/**
	 * Set the limit and optional offset of the query.
	 * @param integer $limit
	 * @param integer $offset
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
	 * @param integer $page
	 * @param integer $perPage
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
	 * @param mixed $order
	 * @param string $direction
	 * @return self
	 */
	public function orderBy($order, string $direction = self::ASC): self
	{
		$this->order = [];
		if (\is_array($order)) {
			foreach ($order as $key => $value) {
				if (empty($key)) {
					$this->order[] = ['column' => $value, 'direction' => self::ASC];
				} else if ($value == self::ASC || $value == self::DESC) {
					$this->order[] = ['column' => $key, 'direction' => $value];
				}
			}
		} else {
			$this->order[] = ['column' => $order, 'direction' => $direction];
		}
		return $this;
	}

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
				$sql .= \implode(', ', \array_map(function (array $field) use ($sql) {
					$sql .= " {$field['column']}";
					if (isset($field['alias'])) {
						$sql .= " AS {$field['alias']}";
					}
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

		// Add updates
		$this->placeholders = [];
		// INSERT has the list of columns SET as a delimiter and the values inside parenthesis
		//	A 2D array of array is expected the nested array representing a single group of values
		if ($this->type == self::INSERT && \count($this->inserts) > 0) {
			$first = false;
			$columns = [];
			$validGroups = 0;
			// Array of array of values
			foreach ($this->inserts as $group) {
				// Skip if it's an empty array
				if (\is_array($group) && \count($group) > 0) {
					// Get a list of all the columns from the first set of values
					if (!$first) {
						foreach ($group as $key => $value) {
							$columns[] = $key;
						}
						$first = true;
					}
					$validGroups++;
				}
			}
			if ($validGroups > 0) {
				$length = \count($columns);
				$columns = \implode(', ', $columns);
				// Generate array of question mark for placeholders
				$placeholders = \implode(', ', \array_fill(0, $length, '?'));
				// Generate the list of values group e.g (?, ?, ?), (?, ?, ?)
				$values = \implode(', ', \array_fill(0, $validGroups, "({$placeholders})"));
				$sql .= "({$columns}) SET {$values}";
			}
		}

		// UPDATE has a SET delimiter and a list of assignments
		//	A 1D array is expected, unlike in INSERT
		if ($this->type == self::UPDATE && \count($this->updates) > 0) {
			$assignments = [];
			foreach ($this->updates as $key => $value) {
				$assignments[] = "{$key} = ?";
				$this->placeholders[] = $value;
			}
			$assignments = \implode(', ', $assignments);
			$sql .= " SET {$assignments}";
		}

		// Add conditions
		if ($this->type != self::INSERT && \count($this->conditions) > 0) {
			$conditions = [];
			foreach ($this->conditions as $group) {
				$conditions[] = "{$group['column']} {$group['operator']} ?";
				$this->placeholders[] = $group['value'];
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
	 */
	private function execute(): bool
	{
		$query = $this->build();
		$this->statement = Database::connection()->prepare($query);
		if ($this->statement === false) {
			throw new QueryException($this, 'Failed to prepare statement.');
		}
		$result = $this->statement->execute($this->placeholders);
		if ($result === false) {
			throw new SQLException($this->statement);
		}
		return true;
	}

	/**
	 * Execute the Query and return the first returned row or false if it does not exists.
	 * @param string|int $classOrMode A PDO mode or a Classname
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
				$this->result = $this->statement->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $classOrMode);
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
	 * @param string|int $classOrMode A PDO mode or a Classname
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
				$this->result = $this->statement->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $classOrMode);
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
