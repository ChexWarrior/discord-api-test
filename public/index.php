<?php declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function(Request $request, Response $response, $args) {
    $response->getBody()->write('Hello Joan!');
    return $response;
});

$app->run();
