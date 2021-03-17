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
		$this->list[\mb_strtolower($name)] = $value;
	}

	public function has($name, $value = null)
	{
		$name = \mb_strtolower($name);
		$exists = \array_key_exists($name, $this->list);
		if ($exists && $value !== null) {
			return $this->list[$name] == $value;
		}
		return $exists;
	}

	public function get($name)
	{
		$name = \mb_strtolower($name);
		if (\array_key_exists($name, $this->list)) {
			return $this->list[$name];
		}
		return false;
	}

	public function all()
	{
		$headers = [];
		foreach ($this->list as $name => $value) {
			$headers[\ucwords($name, '-')] = $value;
		}
		return $headers;
	}
}
