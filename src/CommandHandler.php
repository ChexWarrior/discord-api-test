<?php declare(strict_types=1);

namespace Chexwarrior;

use GuzzleHttp\Client;

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
        return 'List Commands called!';
    }

    public function deleteCommand() {
        return 'Delete Command called!';
    }
}
