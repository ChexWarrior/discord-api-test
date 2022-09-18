<?php declare(strict_types=1);

namespace Chexwarrior;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Response;

class CommandHandler
{
    private Client $client;

    public function __construct(string $appId, string $botToken, string $guildId) {
        $this->client = new Client([
            'base_uri' => "https://discord.com/api/v10/applications/$appId/guilds/$guildId/commands",
            'headers' => [
                'Authorization' => "Bot $botToken",
            ]
        ]);
    }

    public function createCommand() {
        return 'Create Command called!';
    }

    public function listCommands() {
        // $response = $this->makeRequest('/', 'GET');
        // $commandInfo = [];
        // $body = $response->getBody()->getContents();
        // $jsonBody = json_decode(json: $body, associative: true);

        // foreach ($jsonBody as $command) {
        //     $commandInfo[] = [
        //         'name' => $command['name'],
        //         'id' => $command['id'],
        //     ];
        // }

        return 'List Command called!';
    }

    public function deleteCommand() {
        return 'Delete Command called!';
    }

    private function makeRequest(string $url, string $type, array $options = []): Response {
        try {
            $response = $this->client->request($type, $url, $options);
        } catch (ClientException $e) {
            echo Message::toString($e->getRequest());
            echo Message::toString($e->getResponse());
        }

        return $response;
    }
}
