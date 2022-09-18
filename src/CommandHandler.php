<?php declare(strict_types=1);

use GuzzleHttp\Client;

class CommandHandler
{
    private Client $client;

    public function __construct(string $appId, string $botToken, string $guildId)
    {
        $this->client = new Client([
            'base_uri' => "https://discord.com/api/v10/applications/$appId/guilds/$guildId/commands",
            'headers' => [
                'Authorization' => "Bot $botToken",
            ]
        ]);
    }
}
