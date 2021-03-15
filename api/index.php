<?php require_once __DIR__ . '/../src/autoload.php';

// Set ExceptionHandler
// \set_exception_handler(["ExceptionHandler", "handle"]);
// \set_error_handler("ErrorHandler::handle");

/**
 * Configuration files
 * 	Each file in the /config folder is a key in the $config object
 * 	With the value being the returned value in the configuration file
 */
$config = [];
// Read each configuration files in /config
foreach (new DirectoryIterator('..' . DIRECTORY_SEPARATOR . 'config') as $file) {
	// Skip . and ..
	if ($file->getFilename() != '.' && $file->getFilename() != '..') {
		// Filename withtout extension
		$fileName = $file->getBasename('.' . $file->getExtension());
		$config[$fileName] = include $file->getPathname();
	}
}

// All routes
$router = new Camagru\Router('/api');
$router->get('/status', 'Status@status');
$router->get('/{id}', 'Status@status');
$router->post('/login', 'Authentification@login');
$router->post('/register', 'Authentification@register');

// Create and start App
//echo '<pre>';
$app = new Camagru\Application($router);
$app->run();
//echo '</pre>';
