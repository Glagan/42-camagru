<?php

class Env
{
	static $config = [];

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
		if (!isset(static::$config[$namespace][$field])) {
			return static::$config[$namespace][$field];
		}
		return $default;
	}

	public static function getNamespace(string $namespace)
	{
		return static::$config[$namespace];
	}
}
