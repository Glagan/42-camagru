<?php

\spl_autoload_register(function ($className) {
	// echo 'trying to load [' . $className . "]\n";
	$file = __DIR__ . '\\' . $className . '.php';
	$file = \str_replace('\\', DIRECTORY_SEPARATOR, $file);
	if (\file_exists($file)) {
		require $file;
	}
});
