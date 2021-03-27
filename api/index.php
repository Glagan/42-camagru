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

// Configuration
$root = \dirname(__DIR__);
Env::load($root . '/config.ini');
Env::set('Camagru', 'root', $root);

// All routes
$router = new Camagru\Router('/api');

// User
$router->group(Controller\Authentication::class, function ($router) {
	$router->post('/register', ['auth' => false, 'use' => 'register']); // TODO: mail
	$router->post('/login', ['auth' => false, 'use' => 'login']);
	$router->delete('/logout', ['auth' => true, 'use' => 'logout']);
	$router->delete('/logout/all', ['auth' => true, 'use' => 'logoutAll']);
	$router->delete('/logout(?:/{session:.+})?', ['auth' => true, 'use' => 'logout']);
	$router->patch('/send-verification', ['auth' => true, 'use' => 'sendVerification']); // TODO: mail
	$router->post('/forgot-password', ['auth' => false, 'use' => 'resetPassword']); // TODO: mail
	$router->get('/status', ['use' => 'status']);
});

// Account
$router->group(Controller\Account::class, function ($router) {
	$router->patch('/account/update', ['auth' => true, 'use' => 'update']);
});

// Uploads
$router->group(Controller\Upload::class, function ($router) {
	$router->get('/uploads/{id}', ['noPrefix' => true, 'use' => 'single']);
});

// Image
$router->group(Controller\Image::class, function ($router) {
	$router->post('/upload', ['auth' => true, 'use' => 'upload']); // TODO
	$router->get('/list(?:/{page})?', ['use' => 'list']); // TODO
	$router->get('/user/{id}(?:/{page})?', ['use' => 'user']);
	$router->put('/{id}/like', ['auth' => true, 'use' => 'like']);
	$router->post('/{id}/comment', ['auth' => true, 'use' => 'comment']);
	$router->get('/{id}', ['use' => 'single']);
});

// Create and start App
$request = Camagru\Http\Request::make();
$app = new Camagru\Application($router, $root);
$response = $app->run($request);
$response->prepare($request);
$response->render();
