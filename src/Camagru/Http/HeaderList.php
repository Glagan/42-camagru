<?php namespace Camagru\Http;

class HeaderList
{
	/**
	 * @var string[]
	 */
	protected $list;

	public function __construct($list = [])
	{
		$this->list = $list;
	}

	/**
	 * Add the Header to the list.
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function add(string $name, string $value): void
	{
		$this->list[\mb_strtolower($name)] = $value;
	}

	/**
	 * Check if the Header is present in the list.
	 * If value is different to null, it's equality is also checked.
	 * @param string $name
	 * @param string|null $value
	 * @return boolean
	 */
	public function has(string $name, $value = null): bool
	{
		$name = \mb_strtolower($name);
		$exists = \array_key_exists($name, $this->list);
		if ($exists && $value !== null) {
			return $this->list[$name] == $value;
		}
		return $exists;
	}

	/**
	 * Check if the Header exists (case insensitive) and return it's value or false.
	 * @param string $name
	 * @return string|false
	 */
	public function get(string $name)
	{
		$name = \mb_strtolower($name);
		if (\array_key_exists($name, $this->list)) {
			return $this->list[$name];
		}
		return false;
	}

	/**
	 * Return all Headers as a raw array.
	 * Names are capitalized.
	 * @return string[]
	 */
	public function all(): array
	{
		$headers = [];
		foreach ($this->list as $name => $value) {
			$headers[\ucwords($name, '-')] = $value;
		}
		return $headers;
	}
}
