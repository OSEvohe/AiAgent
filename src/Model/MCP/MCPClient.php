<?php

namespace App\Model\MCP;

use App\Model\Tool\AITool;
use PhpMcp\Client\Client;
use PhpMcp\Client\JsonRpc\Results\CallToolResult;
use PhpMcp\Client\Model\Definitions\ToolDefinition;
use PhpMcp\Client\ServerConfig;
use Throwable;
use PhpMcp\Client\Model\Capabilities as ClientCapabilities;

abstract class MCPClient
{
    protected Client $client;
    protected ServerConfig $serverConfig;

    /**
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $this->serverConfig = $this->createServerConfig();

        $clientCapabilities = ClientCapabilities::forClient(); // Default client caps

        $this->client = Client::make()
            ->withClientInfo($this->getClientName(), $this->getClientVersion())
            ->withCapabilities($clientCapabilities)
            ->withServerConfig($this->serverConfig)
            ->build();

        try {
            $this->client->initialize();
        } catch (Throwable $e) {
            throw new \RuntimeException('Failed to connect to MCP server: ' . $e->getMessage());
        }
    }

    abstract protected function createServerConfig(): ServerConfig;

    abstract protected function getClientName(): string;

    abstract protected function getClientVersion(): string;

    /**
     * @return ToolDefinition[]
     */
    public function listTools(): array
    {
        try {
            return $this->client->listTools();
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
}

