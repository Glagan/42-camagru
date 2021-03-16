<?php namespace Camagru;

use Database;
use Exception\SQLException;
use Log;
use SQL\Operator;

class Model
{
	protected $attributes;
	protected $dirty;
	protected static $fields = [];
	protected static $defaults = [];
	protected static $casts = [];

	public function __construct($attributes = [])
	{
		$this->attributes = $attributes;
		$this->dirty = [];
	}

	public function __set($name, $value)
	{
		if ($name != 'id' && \array_key_exists($name, $this->attributes)) {
			if (!\in_array($name, $this->dirty) && $this->attributes[$name] != $value) {
				$this->dirty[] = $name;
			}
		}
		$this->attributes[$name] = $value;
	}

	public function __get($name)
	{
		if (\array_key_exists($name, $this->attributes)) {
			return $this->attributes[$name];
		}
		if (\array_key_exists($name, static::$defaults)) {
			return static::$defaults[$name];
		}
		return null;
	}

	/**
	 * Table name is the class pluralized to lower case.
	 * @return string
	 */
	protected static function getTable(): string
	{
		$class = \explode('\\', \get_called_class());
		return \mb_strtolower(\array_pop($class)) . 's';
	}

	/**
	 * Query the Database on the Model table to find if there is a Model with the given ID.
	 * Returns false if the Model doesn't exists.
	 * @param integer $id
	 * @return \Model|false
	 */
	public static function get(int $id)
	{
		$table = static::getTable();
		$query = "SELECT * FROM {$table} WHERE id = ?";
		Log::debug('Executing ' . $query, [$id]);
		$statement = Database::connection()->prepare($query);
		if ($statement !== false) {
			if ($statement->execute([$id])) {
				$result = $statement->fetch(\PDO::FETCH_ASSOC);
				$class = static::class;
				return new $class($result);
			}
		}
		return false;
	}

	/**
	 * Find all Models that match the given selectors in the Model table.
	 * Selectors format:
	 * 	['column', 'value']
	 * 	['column', 'operator', 'value']
	 * List of operators: =, !=, >, >=, <, <=, IN, NOT IN, IS NULL, IS NOT NULL
	 * 	See SQL\Operator
	 * IS_NULL and IS_NOT_NULL does not require a third parameter.
	 * @param array $selectors Array of selectors
	 * @return \Model[]
	 */
	// TODO: Filter fields with static::$fields
	public static function where(array $selectors = []): array
	{
		// Build the $where array from each selectors
		$where = [];
		$placeholders = [];
		$currentGroup = [];
		foreach ($selectors as $selector) {
			if (\is_array($selector)) {
				$arguments = \count($selector);
				// TODO: Check if $selector contains an array for a OR group
				$column = $selector[0];
				$operator = $selector[1];
				if ($arguments == 2) {
					if ($operator == Operator::IS_NULL || $operator == Operator::IS_NOT_NULL) {
						$where[] = "{$column} {$operator}";
					} else {
						$where[] = "{$column} = ?";
						// TODO: Casted values with static::$casts (for Dates)
						$placeholders[] = $operator;
					}
				} else if ($operator == Operator::IN || $operator == Operator::NOT_IN) {
					// TODO: handle array in $selector[2]
				} else {
					// TODO: Casted values with static::$casts (for Dates)
					$where[] = "{$column} {$operator} ?";
					$placeholders[] = $selector[2];
				}
			}
		}
		// Build the query
		$table = static::getTable();
		$query = "SELECT * FROM {$table}";
		if (\count($where) > 0) {
			$where = \implode(' AND ', $where);
			$query = "{$query} WHERE {$where}";
		}
		Log::debug('Executing ' . $query, $placeholders);
		// Execute the query
		$statement = Database::connection()->prepare($query);
		if ($statement !== false) {
			if ($statement->execute($placeholders)) {
				$results = $statement->fetchAll(\PDO::FETCH_ASSOC);
				$classes = [];
				foreach ($results as $result) {
					$class = static::class;
					$classes[] = new $class($result);
				}
				return $classes;
			}
		}
		throw new SQLException($statement);
	}

	/**
	 * Persist the model to the Database.
	 * Insert it and assign the new ID if it currently doesn't exists.
	 * Return true if the Model was inserted or false if it was updated.
	 * @return bool
	 */
	// TODO: Filter inserted and updated fields with static::$fields
	public function persist(): bool
	{
		$table = $this->getTable();
		$retValue = false;
		// INSERT if there is no 'id' attribute and the Model doesn't exists in the Database
		if (!\array_key_exists('id', $this->attributes)) {
			$columns = \implode(', ', \array_keys($this->attributes));
			$placeholders = \implode(', ', \array_fill(0, \count($this->attributes), '?'));
			$query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
			// TODO: Casted values with static::$casts (for Dates)
			$values = \array_values($this->attributes);
			$statement = Database::connection()->prepare($query);
			$result = $statement->execute($values);
			if ($result) {
				$this->id = Database::lastId();
			}
			$retValue = true;
		}
		// Or only UPDATE if there is a least one dirty field
		else if (\count($this->dirty) > 0) {
			$fields = [];
			$values = [];
			foreach ($this->dirty as $field) {
				$values[] = $this->attributes[$field];
				$fields[] = "{$field} = ?";
			}
			$values[] = $this->id;
			$fields = \implode(', ', $fields);
			$placeholders = \implode(', ', \array_fill(0, \count($this->dirty), '?'));
			$query = "UPDATE {$table} SET {$fields} WHERE id = ?";
			$statement = Database::connection()->prepare($query);
			// TODO: Casted values with static::$casts (for Dates)
			$result = $statement->execute($values);
			$retValue = false;
		}
		// Remove dirty or throw if there was a query and it failed
		if ($result) {
			$this->dirty = [];
		} else if ($statement) {
			throw new SQLException($statement);
		}
		return $retValue;
	}

	/**
	 * Delete the model from the Database.
	 * Return true if it was deleted or false if there is no ID.
	 * @return boolean
	 */
	public function delete(): bool
	{
		if ($this->id) {
			$table = $this->getTable();
			$query = "DELETE FROM {$table} WHERE id = ?";
			$statement = Database::connection()->query($query);
			if ($statement && $statement->execute([$this->id])) {
				$this->id = null;
				return true;
			}
			throw new SQLException($statement);
		}
		return false;
	}
}
