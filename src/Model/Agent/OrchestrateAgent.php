<?php

namespace App\Model\Agent;

use App\Model\IO\IOInterface;
use App\Model\MCP\McpClient;
use App\Model\Tool\AgentTool;

class OrchestrateAgent implements Agent
{
    private string $mcpJsonConfig = <<<JSON
{
  "mcpServers": {
    "sequential-thinking": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-sequential-thinking"
      ]
    }
  }
}
JSON;

    /**
     * @param Agent[] $agents
     */
    public function __construct(private array $agents = [], private IOInterface $io)
    {
    }


    public function getName(): string
    {
        return 'OrchestrateAgent';
    }

    public function getDescription(): string
    {
        return 'An agent that orchestrates tasks across multiple agents, coordinating their actions to achieve complex goals.';
    }

    public function getTools(): array
    {
        $tools = [];
        foreach ($this->agents as $agent) {
            $tools[] = new AgentTool($this->io, $agent);
        }

        return $tools;
    }

    public function getMcps(): array
    {
        return McpClient::fromJsonConfig($this->mcpJsonConfig);
    }
}
