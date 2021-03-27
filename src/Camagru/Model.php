<?php namespace Camagru;

use Database;
use SQL\Query;
use SQL\Value;

class Model
{
	/**
	 * @var array
	 */
	protected $attributes;
	/**
	 * @var array
	 */
	protected $dirty;
	/**
	 * @var array
	 */
	protected static $fields = [];
	/**
	 * @var array
	 */
	protected static $defaults = [];
	/**
	 * @var array
	 */
	protected static $casts = [];

	public function __construct(array $attributes = [])
	{
		$this->attributes = [];
		foreach ($attributes as $name => $value) {
			$this->__set($name, $value);
		}
		$this->dirty = [];
	}

	/**
	 * Set the associated value to the name column.
	 * If there is a cast, the value is casted before being assigned.
	 * If there is already a value associated with name, the column is added to the dirty list.
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set(string $name, $value)
	{
		// Cast value if necessary
		if (\array_key_exists($name, static::$casts)) {
			$type = static::$casts[$name];
			if ($type == 'bool') {
				$value = !!$value;
			} else if ($type == 'date') {
				if (!($value instanceof \DateTime)) {
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

	/**
	 * Return the column value, if it doesn't exists it's default value if there is one, or else null.
	 * @param string $name
	 * @return mixed
	 */
	public function __get(string $name)
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
	 * @param array|string $order
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
	 * @param array $conditions Array of selectors
	 * @param array|string $order
	 * @param int $limit
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
	 * Count the number of Models that match the given selectors in the Model table.
	 * @see SQL\Query
	 * @param array $conditions Array of selectors
	 * @return int
	 */
	public static function count(array $conditions): int
	{
		$query = static::select()
			->columns(['totalCount' => Value::make('COUNT(*)')])
			->where($conditions);
		$total = $query->first();
		return $total['totalCount'];
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
	 * @return bool
	 */
	public function remove(): bool
	{
		if ($this->id) {
			$query = static::delete()
				->where(['id' => $this->id]);
			if ($query->execute()) {
				$this->id = null;
				$this->attributes = [];
				return true;
			}
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

	/**
	 * JSON encode the Model attributes.
	 * @return string
	 */
	public function __toString(): string
	{
		return \json_encode($this->toArray());
	}
}
