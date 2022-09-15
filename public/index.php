<?php declare(strict_types=1);

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

$app = AppFactory::create();
$twigLoader = new FilesystemLoader('./templates');

$app->get('/', function(Request $request, Response $response, $args) use ($twigLoader) {
    $template = new Environment($twigLoader);

    $response->getBody()->write($template->render('home.twig', [
        'title' => 'DISCORD API TEST',
    ]));

    return $response;
});

$app->post('/create', function (Request $request, Response $response) use ($appId, $botToken, $guildId) {
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

    $url = "https://discord.com/api/v10/applications/$appId/commands";
    $client =  new Client();
    $response = $client->post($url, [
        'json' => $data,
        'headers' => [
            'Authorization' => "Bot $botToken",
        ]
    ]);

    return $response->withStatus(302)->withHeader('Location', '/');
});

$app->run();
