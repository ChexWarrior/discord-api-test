<?php declare(strict_types=1);

use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function(Request $request, Response $response, $args) {
    $response->getBody()->write('Hello world!');
    return $response;
});

$app->run();
