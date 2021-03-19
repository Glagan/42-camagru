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
		$this->attributes = [];
		foreach ($attributes as $name => $value) {
			$this->__set($name, $value);
		}
		$this->dirty = [];
	}

	public function __set($name, $value)
	{
		// Cast value if necessary
		if (\array_key_exists($name, static::$casts)) {
			$type = static::$casts[$name];
			if ($type == 'bool') {
				$value = !!$value;
			} else if ($type == 'date') {
				if (!\is_a($value, \DateTime::class)) {
					$value = new \DateTime($value);
				}
			}
		}
		if ($name == 'id') {
			$value = (int) $value;
		} else if (\array_key_exists($name, $this->attributes) && !\in_array($name, $this->dirty) && $this->attributes[$name] != $value) {
			$this->dirty[] = $name;
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
		$query = static::select()
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
	public static function all(array $conditions, $order = [], $limit = -1): array
	{
		$query = static::select()
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
	public function persist(): bool
	{
		// If there is no ID we INSERT the model
		if ($this->id === null) {
			$query = static::insert()
				->set($this->toArray());
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
			$query = static::update()
				->set($updates)
				->where(['id' => $this->id]);
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
	public function remove(): bool
	{
		if ($this->id) {
			$query = static::delete()
				->where(['id' => $this->id]);
			return $query->execute();
		}
		return false;
	}

	/**
	 * Return a new SELECT Query.
	 * @return \SQL\Query
	 */
	public static function select(): Query
	{
		return (new Query(Query::SELECT, static::getTable()));
	}

	/**
	 * Return a new INSERT Query.
	 * @return \SQL\Query
	 */
	public static function insert(): Query
	{
		return (new Query(Query::INSERT, static::getTable()));
	}

	/**
	 * Return a new UPDATE Query.
	 * @return \SQL\Query
	 */
	public static function update(): Query
	{
		return (new Query(Query::UPDATE, static::getTable()));
	}

	/**
	 * Return a new DELETE Query.
	 * @return \SQL\Query
	 */
	public static function delete(): Query
	{
		return (new Query(Query::DELETE, static::getTable()));
	}

	public function toArray(array $pick = []): array
	{
		if (\count($pick) > 0) {
			// @see https://stackoverflow.com/a/46843866/7794671
			return \array_intersect_key($this->attributes, \array_flip($pick));
		}
		return $this->attributes;
	}

	public function __toString()
	{
		return \json_encode($this->toArray());
	}
}
