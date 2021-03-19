<?php namespace Camagru;

use Database;
use SQL\Query;

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
		return static::first(['id' => $id]);
	}

	/**
	 * Return the first found row that meet the conditions.
	 * @param array $conditions
	 * @param int|null $order
	 * @return \Model|false
	 */
	public static function first(array $conditions, $order = [])
	{
		$query = (new Query(Query::SELECT, static::getTable()))
			->where($conditions)
			->orderBy($order)
			->limit(1);
		return $query->first(static::class);
	}

	/**
	 * Find all Models that match the given selectors in the Model table.
	 * @see SQL\Query
	 * @param array $selectors Array of selectors
	 * @return \Model[]
	 */
	// TODO: Filter fields with static::$fields
	public static function all(array $conditions, $order = [], $limit = -1): array
	{
		$query = (new Query(Query::SELECT, static::getTable()))
			->where($conditions)
			->orderBy($order)
			->limit($limit);
		return $query->all(static::class);
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
		// If there is no ID we INSERT the model
		if ($this->id === null) {
			$query = (new Query(Query::INSERT, static::getTable()))
				->insert($this->toArray());
			$result = $query->execute();
			if ($result) {
				$this->id = Database::lastId();
			}
			return $result;
		}
		// Else we only update dirty fields
		else if (\count($this->dirty) > 0) {
			$updates = [];
			foreach ($this->dirty as $field) {
				$updates[$field] = $this->attributes[$field];
			}
			$query = (new Query(Query::UPDATE, static::getTable()))
				->set($updates)
				->insert($this->toArray());
			$result = $query->execute();
			if ($result) {
				$this->dirty = [];
			}
			return $result;
		}
		return true;
	}

	/**
	 * Delete the model from the Database.
	 * Return true if it was deleted or false if there is no ID.
	 * @return boolean
	 */
	public function delete(): bool
	{
		if ($this->id) {
			$query = (new Query(Query::DELETE, static::getTable()))
				->where(['id' => $this->id]);
			return $query->execute();
		}
		return false;
	}

	// TODO: Add defaults + hide hidden fields
	public function toArray(array $fields = []): array
	{
		return $this->attributes;
	}

	public function __toString()
	{
		return \json_encode($this->toArray());
	}
}
