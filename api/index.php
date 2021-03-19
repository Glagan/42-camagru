<?php require_once __DIR__ . '/../src/autoload.php';

\session_name('session');
\session_set_cookie_params([
	'lifetime' => time() + 60 * 60 * 24 * 7, // 1 week
	'path' => '/',
	'domain' => $_SERVER['HTTP_HOST'],
	'secure' => true,
	'httponly' => true,
	'samesite' => 'Strict',
]);
\session_start();

// Set ExceptionHandler
\set_exception_handler(["ExceptionHandler", "handle"]);
\set_error_handler("ErrorHandler::handle");

/**
 * Configuration files
 * 	Each file in the /config folder is a key in the $config object
 * 	With the value being the returned value in the configuration file
 */
foreach (new DirectoryIterator('..' . DIRECTORY_SEPARATOR . 'config') as $file) {
	// Skip . and ..
	if ($file->getFilename() != '.' && $file->getFilename() != '..' && !$file->isDir()) {
		// Filename without extension
		$fileName = $file->getBasename('.' . $file->getExtension());
		Env::setNamespace($fileName, include $file->getPathname());
	}
}

// All routes
$router = new Camagru\Router('/api');
$router->post('/register', ['auth' => false, 'use' => 'Authentication@register']);
$router->post('/login', ['auth' => false, 'use' => 'Authentication@login']);
$router->delete('/logout', ['auth' => true, 'use' => 'Authentication@logout']);
$router->delete('/logout/all', ['auth' => true, 'use' => 'Authentication@logoutAll']);
$router->delete('/logout/{session:.+}?', ['auth' => true, 'use' => 'Authentication@logout']);
$router->get('/profile', ['auth' => true, 'use' => 'Profile@single']);

// Create and start App
//echo '<pre>';
$app = new Camagru\Application($router);
$app->run();
//echo '</pre>';
