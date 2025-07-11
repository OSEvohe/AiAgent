<?php

namespace App\Model\Core\Mcp;

use Exception;
use PhpMcp\Client\Client;
use PhpMcp\Client\Enum\TransportType;
use PhpMcp\Client\JsonRpc\Results\CallToolResult;
use PhpMcp\Client\Model\Capabilities as ClientCapabilities;
use PhpMcp\Client\Model\Definitions\ToolDefinition;
use PhpMcp\Client\ServerConfig;
use Throwable;

class McpClient
{
    protected Client $client;

    /**
     * @throws \RuntimeException
     */
    private function __construct(ServerConfig $serverConfig, private readonly string $name, private readonly string $version = '1.0', private readonly array $excludedTools = [])
    {
        if (McpsPool::hasMcp($this->name)) {
            $this->client = McpsPool::getMcp($this->name);
        } else {
            dump('Connecting to MCP server: ' . $this->getClientName());

            $clientCapabilities = ClientCapabilities::forClient(); // Default client caps

            $this->client = Client::make()
                ->withClientInfo($name, $version)
                ->withCapabilities($clientCapabilities)
                ->withServerConfig($serverConfig)
                ->build();

            try {
                $this->client->initialize();
            } catch (Throwable $e) {
                throw new \RuntimeException('Failed to connect to MCP server: ' . $e->getMessage());
            }

            McpsPool::addMcp($this->name);
        }
    }

    /**
     * @throws Exception
     */
    public static function fromJsonConfig(string $filePath): array
    {
        $jsonConfig = json_decode(self::loadJsonConfig($filePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON configuration: ' . json_last_error_msg());
        }

        $mcpServers = [];
        foreach ($jsonConfig['mcpServers'] as $serverName => $server) {
            if (!isset($server['command']) || !isset($server['args'])) {
                throw new \RuntimeException('Configuration must include "command" and args fields.');
            }

            $serverConfig = self::createServerConfig(
                name: $serverName,
                command: $server['command'],
                args: $server['args'],
                transport: TransportType::from($server['transport'] ?? 'stdio') ?? TransportType::Stdio,
                timeout: $server['timeout'] ?? 600
            );

            $mcpServers[] = new self(serverConfig: $serverConfig, name: $serverName, excludedTools: $server['excluded_tools'] ?? []);
        }

        return $mcpServers;
    }

    /**
     * @throws Exception
     */
    private static function loadJsonConfig(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("Configuration file does not exist: $filePath");
        }

        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            throw new Exception("Failed to read configuration file: $filePath");
        }

        return $jsonContent;
    }

    /**
     * @return ToolDefinition[]
     */
    public function listTools(): array
    {
        try {
            $list = [];
            foreach ($this->client->listTools() as $tool) {
                if (!in_array($tool->name, $this->excludedTools)) {
                    $list[] = $tool;
                }
            }

            return $list;
        } catch (Throwable $e) {
            throw new \RuntimeException('Failed to list tools: ' . $e->getMessage());
        }
    }

    public function __destruct()
    {
        dump('Disconnecting MCP client: ' . $this->getClientName());
        $this->client->disconnect();
    }

    public function callTool(string $toolName, array $arguments): CallToolResult
    {
        try {
            if ($this->client->isReady()) {
                return $this->client->callTool($toolName, $arguments);
            }
        } catch (Throwable $e) {
            throw new \RuntimeException('Failed to call tool: ' . $e->getMessage());
        }
    }

    static protected function createServerConfig(string $name, string $command, array $args, TransportType $transport = TransportType::Stdio, int $timeout = 600): ServerConfig
    {
        return new ServerConfig(
            name: $name,
            transport: $transport,
            timeout: $timeout,
            command: $command,
            args: $args,
        );
    }

    protected function getClientName(): string
    {
        return $this->name;
    }

    protected function getClientVersion(): string
    {
        return $this->version;
    }
}

