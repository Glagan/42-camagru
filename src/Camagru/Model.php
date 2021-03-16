<?php namespace Camagru;

use Database;
use Exception\SQLException;
use Log;

class Model
{
	protected $attributes;
	protected $dirty;

	public function __construct($attributes = [])
	{
		$this->attributes = $attributes;
		$this->dirty = [];
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

	public function persist()
	{
		$table = $this->getTable();
		if (!\array_key_exists('id', $this->attributes)) {
			$columns = \implode(', ', \array_keys($this->attributes));
			$placeholders = \implode(', ', \array_fill(0, \count($this->attributes), '?'));
			$query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
			$values = \array_values($this->attributes);
			$statement = Database::connection()->prepare($query);
			$result = $statement->execute($values);
		} else if (\count($this->dirty) > 0) {
			$fields = [];
			$values = [];
			foreach ($this->dirty as $field) {
				$values[] = $this->attributes[$field];
				$fields[] = "{$field} = ?";
			}
			$values[] = $this->attributes['id'];
			$fields = \implode(', ', $fields);
			$placeholders = \implode(', ', \array_fill(0, \count($this->dirty), '?'));
			$query = "UPDATE {$table} SET {$fields} WHERE id = ?";
			$statement = Database::connection()->prepare($query);
			$result = $statement->execute($values);
		}
		if ($result) {
			$this->dirty = [];
		} else if ($statement) {
			throw new SQLException($statement);
		}
	}

	public function delete(): bool
	{
		if (\array_key_exists('id', $this->attributes)) {
			$table = $this->getTable();
			$query = "DELETE FROM {$table} WHERE id = ?";
			$statement = Database::connection()->query($query);
			if (!$statement->execute([$this->attributes['id']])) {
				throw new SQLException($statement);
			}
			return true;
		}
		return false;
	}
}
