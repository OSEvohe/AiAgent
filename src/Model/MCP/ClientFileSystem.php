<?php

namespace App\Model\MCP;

use PhpMcp\Client\Enum\TransportType;
use PhpMcp\Client\ServerConfig;

class ClientFileSystem extends MCPClient
{
    protected function createServerConfig(): ServerConfig
    {
        $userHome = '/home/sebastien';

        return new ServerConfig(
            name: 'local_filesystem',
            transport: TransportType::Stdio,
            timeout: 60,
            command: 'npx',
            args: [
                '-y',
                '@modelcontextprotocol/server-filesystem',
                $userHome . '/Dev-Tools',
            ],
            workingDir: $userHome
        );
    }

    protected function getClientName(): string
    {
        return 'MyFileSystemApp';
    }

    protected function getClientVersion(): string
    {
        return '1.0';
    }
}
