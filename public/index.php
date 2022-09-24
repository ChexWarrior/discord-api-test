<?php declare(strict_types=1);

use Chexwarrior\CommandHandler;
use Discord\Interaction;
use Discord\InteractionResponseType;
use Discord\InteractionType;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ParagonIE\ConstantTime\Hex;
use ParagonIE\Halite\Asymmetric\Crypto;
use ParagonIE\Halite\Asymmetric\SignaturePublicKey;
use ParagonIE\Halite\Halite;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$appId = $_ENV['APP_ID'];
$botToken = $_ENV['BOT_TOKEN'];
$guildId = $_ENV['GUILD_ID'];
$publicKey = $_ENV['PUBLIC_KEY'];
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
    $commandId ??= $_POST['command-id'];
    $commandResult = $commandAction === 'create' || $commandAction === 'delete' || $commandAction === 'list';
    $requestId = uniqid();

    if (!$commandResult) return $response->withStatus(404);

    if ($commandAction === 'delete') {
        apcu_store("{$requestId}_command_id_delete", $commandId);
    }

    return $response
        ->withStatus(303)
        ->withHeader('Set-Cookie', "requestId=$requestId; HttpOnly")
        ->withHeader('Location', "/action?type=$commandAction");
});

$app->get('/action', function (Request $request, Response $response) use ($commandHandler, $twigLoader) {
    // Ensure action is create, list or delete
    $commandAction ??= $_GET['type'];
    $cookies ??= $request->getHeader('Cookie');
    $requestId = null;

    if (!empty($cookies)) {
        [, $requestId] = explode('=', $cookies[0]);
    }

    if ($commandAction === 'list') {
        $results = $commandHandler->listCommands();
    } else if ($commandAction === 'delete') {
        $commandId = apcu_fetch("{$requestId}_command_id_delete");
        $results = $commandHandler->deleteCommand($commandId);
    } else if ($commandAction === 'create') {
        $results = $commandHandler->createCommand();
    }

    $template = new Environment($twigLoader);
    $response->getBody()->write($template->render('action.twig', [
        'commandAction' => $commandAction,
        'results' => $results,
    ]));

    return $response;
});

$app->post('/interactions', function (Request $request, Response $response) use ($publicKey) {
    // Handle header signature verification
    [$signature] = $request->getHeader('X-Signature-Ed25519');
    [$signatureTimestamp] = $request->getHeader('X-Signature-Timestamp');
    $rawBody = $request->getBody()->getContents();

    $binPublicKey = hex2bin($publicKey);
    $keyData = Halite::HALITE_VERSION_KEYS . $binPublicKey .
        \sodium_crypto_generichash(
            Halite::HALITE_VERSION_KEYS . $binPublicKey,
            '',
            \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX
        );

    $signPublicKey = KeyFactory::importSignaturePublicKey(
        new HiddenString(Hex::encode($keyData))
    );

    if (!Crypto::verify($signatureTimestamp . $rawBody, $signPublicKey, $signature, false)) {
        return $response->withStatus(401);
    }

    $body = json_decode($rawBody, true);

    // Handle ping message
    if (array_key_exists('type', $body) && $body['type'] === InteractionType::PING) {
       $pong = json_encode(['type' => 1]);
       $response->getBody()->write($pong);
       return $response
        ->withStatus(200)
        ->withHeader('Content-Type', 'application/json');
    }

    // Handle player choice in challenge
    if (array_key_exists('type', $body) && $body['type'] === InteractionType::APPLICATION_COMMAND) {
        $playerChoice = strtolower($body['data']['options'][0]['value']);
        $computerChoice = match(random_int(1, 3)) {
            1 => 'rock',
            2 => 'paper',
            3 => 'scissors'
        };

        $msg = determineWinner($playerChoice, $computerChoice);

        $interactionResponse = [
            'type' => InteractionResponseType::CHANNEL_MESSAGE_WITH_SOURCE,
            'data' => [
                'content' => "Computer played $computerChoice! $msg",
            ]
        ];

        $response->getBody()->write(json_encode($interactionResponse));

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
    }

    return $response->withStatus(400);
});

function determineWinner($playerChoice, $computerChoice) {
    $playerWinMsg = 'Player Wins!';
    $computerWinMsg = 'Computer Wins!';

    if ($playerChoice === $computerChoice) {
        return "Draw!";
    }

    if ($playerChoice === 'rock' && $computerChoice === 'paper') {
        return $computerWinMsg;
    }

    if ($playerChoice === 'rock' && $computerChoice === 'scissors') {
        return $playerWinMsg;
    }

    if ($playerChoice === 'paper' && $computerChoice === 'rock') {
        return $playerWinMsg;
    }

    if ($playerChoice === 'paper' && $computerChoice === 'scissors') {
        return $computerWinMsg;
    }

    if ($playerChoice === 'scissors' && $computerChoice === 'paper') {
        return $playerWinMsg;
    }

    if ($playerChoice === 'scissors' && $computerChoice === 'rock') {
        return $computerWinMsg;
    }
}

$app->run();
