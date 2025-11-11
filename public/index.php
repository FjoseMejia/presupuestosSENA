<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';

$routes= require __DIR__ . '/../routes.php';


$uri= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$route= trim($uri, '/');

$method= $_SERVER['REQUEST_METHOD'];
$key= ($method === 'POST') ? "$route-post" : $route;

if (isset($routes[$key])) {
    $routes[$key]();
}else {
    //http_response_code(404);
    $file404 = __DIR__ . '/../app/view/errors/404.php';
    
    if (file_exists($file404)) {
        require $file404;
    } else {
        echo "Página no encontrada: /$file404";
    }
}

