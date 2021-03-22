<?php

class Env
{
	/**
	 * @var array
	 */
	static $config = [];

	/**
	 * Load the config.ini file located in $iniFile.
	 * If there is an error while parsing the function silently fails.
	 * @param string $iniFile
	 * @return void
	 */
	public static function load(string $iniFile): void
	{
		$config = \parse_ini_file($iniFile, true);
		if ($config === false) {
			Log::debug('Could not read config.ini.');
		} else {
			foreach ($config as $key => $value) {
				Env::setNamespace($key, $value);
			}
		}
	}

	/**
	 * Set the value of a parameter in a namespace.
	 * @param string $namespace
	 * @param string $field
	 * @param mixed $value
	 * @return void
	 */
	public static function set(string $namespace, string $field, $value): void
	{
		if (!isset(static::$config[$namespace])) {
			static::$config[$namespace] = [];
		}
		static::$config[$namespace][$field] = $value;
	}

	/**
	 * Replace the entire list of parameters for namespace.
	 * @param string $namespace
	 * @param array $value
	 * @return void
	 */
	public static function setNamespace(string $namespace, array $value): void
	{
		static::$config[$namespace] = $value;
	}

	/**
	 * @param string $namespace
	 * @param string $field
	 * @param boolean $default
	 * @return mixed
	 */
	public static function get(string $namespace, string $field, $default = false)
	{
		if (isset(static::$config[$namespace][$field])) {
			return static::$config[$namespace][$field];
		}
		return $default;
	}

	/**
	 * Return a whole namespace parameters.
	 * @param string $namespace
	 * @return array
	 */
	public static function getNamespace(string $namespace)
	{
		if (isset(static::$config[$namespace])) {
			return static::$config[$namespace];
		}
		return [];
	}
}
