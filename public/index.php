<?php declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$twigLoader = new FilesystemLoader('./templates');

$app->get('/', function(Request $request, Response $response, $args) use ($twigLoader) {
    $template = new Environment($twigLoader);
    $response->getBody()->write($template->render('home.twig', [
        'title' => 'DISCORD API TEST',
    ]));

    return $response;
});

$app->run();
