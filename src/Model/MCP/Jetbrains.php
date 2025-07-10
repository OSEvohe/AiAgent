<?php

namespace App\Model\MCP;

use PhpMcp\Client\Enum\TransportType;
use PhpMcp\Client\ServerConfig;

class Jetbrains extends MCPClient
{
    protected function createServerConfig(): ServerConfig
    {
        return new ServerConfig(
            name: 'jetbrains',
            transport: TransportType::Stdio,
            timeout: 60,
            command: 'npx',
            args: [
                '-y',
                '@jetbrains/mcp-proxy',
            ],
        );
    }

    protected function getClientName(): string
    {
        return 'Jetbrains';
    }

    protected function getClientVersion(): string
    {
        return '1.0';
    }
}
