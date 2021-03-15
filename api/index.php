<?php require_once __DIR__ . '/src/autoload.php';

// Read configuration files
$config = [];
// ...

// Set ExceptionHandler
\set_exception_handler(["ExceptionHandler", "handle"]);
\set_error_handler("ErrorHandler::handle");

// Create and start App
$app = new Camagru\Application();
$app->run();
