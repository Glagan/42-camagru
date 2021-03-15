<?php namespace Camagru\Http;

class HeaderList
{
	protected $list;

	public function __construct($list = [])
	{
		$this->list = $list;
	}

	public function add($name, $value)
	{
		$this->list[$name] = $value;
	}

	public function has($name, $value = null)
	{
		$exists = \array_key_exists($name, $this->list);
		if ($exists && $value !== null) {
			return $this->list[$name] == $value;
		}
		return $exists;
	}

	public function get($name)
	{
		if (\array_key_exists($name, $this->list)) {
			return $this->list[$name];
		}
		return false;
	}

	public function all()
	{
		return $this->list;
	}
}
