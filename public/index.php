<?php declare(strict_types=1);

use Chexwarrior\CommandHandler;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$appId = $_ENV['APP_ID'];
$botToken = $_ENV['BOT_TOKEN'];
$guildId = $_ENV['GUILD_ID'];
$commandHandler = new CommandHandler($appId, $botToken, $guildId);
$app = AppFactory::create();
$twigLoader = new FilesystemLoader('./templates');

/**
 * Sample Application to Understand Discord API
 *
 * Goal: Create Rock Paper Scissors Game
 *
 * Create buttons to allow us to: Create, Remove and See all commands we've created, also the Create method should let us know if we've already created one
 */
$app->get('/', function(Request $request, Response $response, $args) use ($twigLoader) {
    $template = new Environment($twigLoader);
    $response->getBody()->write($template->render('home.twig'));

    return $response;
});

$app->post('/command', function (Request $request, Response $response) {
    $commandAction ??= $_POST['command-action'];
    $commandResult = $commandAction === 'create' || $commandAction === 'delete' || $commandAction === 'list';

    if (!$commandResult) return $response->withStatus(404);

    return $response
        ->withStatus(302)
        ->withHeader('Location', "/action?type=$commandAction");
});

$app->get('/action', function (Request $request, Response $response) use ($commandHandler, $twigLoader) {
    // Ensure action is create, list or delete
    $commandAction ??= $_GET['type'];

    if ($commandAction === 'list') {
        $results = $commandHandler->listCommands();
    } else if ($commandAction === 'delete') {
        $commandId ??= urlencode($_GET['commandId']);
        $results = $commandHandler->deleteCommand($commandId);
    } else if ($commandAction === 'create') {
        $results = $commandHandler->createCommand();
    }


    if (empty($results)) return $response->withStatus(404);

    $template = new Environment($twigLoader);
    $response->getBody()->write($template->render('action.twig', [
        'commandAction' => $commandAction,
        'results' => $results,
    ]));

    return $response;
});

$app->run();
