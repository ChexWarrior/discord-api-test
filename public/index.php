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

    $response->getBody()->write($template->render('home.twig', [
        'title' => 'DISCORD API TEST',
    ]));

    return $response;
});

$app->post('/command', function (Request $request, Response $response) use ($appId, $botToken, $guildId) {
    $data = [
        'name' => 'test',
        'type' => 1,
        'description' => 'Random test command',
        'options' => [
            [
                'name' => 'random',
                'description' => 'Random option',
                'type' => 3,
                'required' => true,
                'choices' => [
                    [
                        'name' => 'Rock',
                        'value' => 'rock',
                    ],
                    [
                        'name' => 'Paper',
                        'value' => 'paper',
                    ],
                    [
                        'name' => 'Scissors',
                        'value' => 'scissors'
                    ]
                ]
            ],
            [
                'name' => 'hello',
                'description' => 'another option',
                'type' => 5,
                'required' => false,
            ]
        ],
    ];

    $url = "https://discord.com/api/v10/applications/$appId/guilds/$guildId/commands";
    $client =  new Client();
    try {
        $discordResponse = $client->post($url, [
            'json' => $data,
            'headers' => [
                'Authorization' => "Bot $botToken",
            ]
        ]);
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    return $response->withStatus(302)->withHeader('Location', '/');
});

$app->run();
