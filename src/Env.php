<?php

class Env
{
	static $config = [];

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

	public static function set(string $namespace, string $field, $value): void
	{
		if (!isset(static::$config[$namespace])) {
			static::$config[$namespace] = [];
		}
		static::$config[$namespace][$field] = $value;
	}

	public static function setNamespace(string $namespace, array $value): void
	{
		static::$config[$namespace] = $value;
	}

	public static function get(string $namespace, string $field, $default = false)
	{
		if (isset(static::$config[$namespace][$field])) {
			return static::$config[$namespace][$field];
		}
		return $default;
	}

	public static function getNamespace(string $namespace)
	{
		if (isset(static::$config[$namespace])) {
			return static::$config[$namespace];
		}
		return [];
	}
}
