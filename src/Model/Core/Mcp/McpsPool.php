<?php

namespace App\Model\Core\Mcp;

use PhpMcp\Client\Client;

class McpsPool
{
    static private array $mcps = [];

    public static function addMcp(string $name, Client $client): void
    {
        if (!isset(self::$mcps[$name])) {
            self::$mcps[$name] = $client;
        }
    }

    public static function getMcp(string $name): ?Client
    {
        return self::$mcps[$name] ?? null;
    }

    public static function hasMcp(string $name): bool
    {
        return isset(self::$mcps[$name]);
    }
}
