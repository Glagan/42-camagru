<?php require_once __DIR__ . '/../src/autoload.php';

// Create Request and set Session default Cookie
$request = Camagru\Http\Request::make();
\session_name('session');
\session_set_cookie_params([
	'lifetime' => time() + 60 * 60, // 1 hour
	'path' => '/',
	'domain' => $request->getCookieDomain(),
	'secure' => $request->isSecure(),
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
Env::set('Camagru', 'storage', $root . '/storage');
Env::set('Camagru', 'tmp', $root . '/storage/tmp');
Env::set('Camagru', 'decorations', $root . '/storage/decorations');
Env::set('Camagru', 'uploads', $root . '/storage/uploads');

// All routes
$router = new Camagru\Router('/api');

// User
$router->group(Controller\Authentication::class, function ($router) {
	$router->post('/register', ['auth' => false, 'use' => 'register']);
	$router->post('/login', ['auth' => false, 'use' => 'login']);
	$router->delete('/logout', ['auth' => true, 'use' => 'logout']);
	$router->delete('/logout/all', ['auth' => true, 'use' => 'logoutAll']);
	$router->delete('/logout(?:/{session:.+})?', ['auth' => true, 'use' => 'logout']);
	$router->get('/status', ['use' => 'status']);
});

// Account
$router->group(Controller\Account::class, function ($router) {
	$router->post('/account/forgot-password', ['auth' => false, 'use' => 'sendResetPassword']);
	$router->patch('/account/reset-password', ['auth' => false, 'use' => 'resetPassword']);
	$router->put('/account/send-verification', ['auth' => true, 'use' => 'sendVerification']);
	$router->patch('/account/verify', ['auth' => true, 'use' => 'verify']);
	$router->patch('/account/update', ['auth' => true, 'use' => 'update']);
	$router->delete('/account', ['auth' => true, 'use' => 'deleteAccount']);
});

// Creations
$router->post('/upload', ['auth' => true, 'use' => 'Controller\Create@upload']);
$router->group(Controller\Creations::class, function ($router) {
	$router->get('/list(?:/{page})?', ['use' => 'list']);
	$router->get('/user/{id}(?:/{page})?', ['use' => 'user']);
	$router->put('/{id}/like', ['auth' => true, 'use' => 'like']);
	$router->post('/{id}/comment', ['auth' => true, 'use' => 'comment']);
	$router->delete('/{id}', ['auth' => true, 'use' => 'deleteSingle']);
	$router->get('/{id}', ['use' => 'single']);
});

// Decorations
$router->group(Controller\Decorations::class, function ($router) {
	$router->get('/decorations/{category:still|animated}', ['use' => 'filtered']);
	$router->get('/decorations', ['use' => 'list']);
});

// Create and start App
$app = new Camagru\Application($router, $root);
$response = $app->run($request);
$response->prepare($request);
$response->render();
