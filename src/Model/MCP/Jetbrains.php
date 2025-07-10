<?php

namespace App\Model\MCP;

use PhpMcp\Client\Enum\TransportType;
use PhpMcp\Client\ServerConfig;

/**
 * Jetbrains MCP Client
 * This class provides configuration for the Jetbrains MCP client.
 */
class Jetbrains extends MCPClient
{
    /**
     * Create server configuration
     * This method initializes and returns a ServerConfig object
     * with specific settings for the Jetbrains MCP client.
     *
     * @return ServerConfig
     */
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

    /**
     * Get client name
     * This method returns the name of the client.
     *
     * @return string
     */
    protected function getClientName(): string
    {
        return 'Jetbrains';
    }

    /**
     * Get client version
     * This method returns the version of the client.
     *
     * @return string
     */
    protected function getClientVersion(): string
    {
        return '1.0';
    }
}
