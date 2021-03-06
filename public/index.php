<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use App\Utils\ServerLogger;
use ArgumentsResolver\InDepthArgumentsResolver;
use ArgumentsResolver\NamedArgumentsResolver;



/**
 * Define STDIN, STDOUT and STDERR stream output for PHP built-in web server
 * And
 * Some configurations for PHP built-in web server
 */
if (!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'rb'));
if (!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'wb'));
if (!defined('STDERR')) define('STDERR', fopen('php://stderr', 'wb'));

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
  $r->addRoute('GET', '/', 'App\Controllers\EmployeeController/index');
  $r->addRoute('GET', '/employees', 'App\Controllers\EmployeeController/index');
  $r->addRoute('POST', '/employees/add', 'App\Controllers\EmployeeController/add');
  $r->addRoute('POST', '/employees/edit', 'App\Controllers\EmployeeController/edit');
});

$container = new DI\Container();


// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
  $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
  case FastRoute\Dispatcher::NOT_FOUND:
    // ... 404 Not found
    break;
  case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
    $allowedMethods = $routeInfo[1];
    // ... 405 Method Not Allowed
    break;
  case FastRoute\Dispatcher::FOUND:
    $handler = $routeInfo[1];
    $vars = ($httpMethod == 'POST') ? $_POST : $routeInfo[2];
    list($class, $method) = explode("/", $handler, 2);
    ServerLogger::log("=> Form Vars:", $vars);
    call_user_func_array(array($container->get($class), $method), (new NamedArgumentsResolver([$container->get($class), $method]))->resolve($vars));
    break;
}
