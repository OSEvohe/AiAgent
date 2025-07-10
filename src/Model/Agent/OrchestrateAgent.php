<?php

namespace App\Model\Agent;

use App\Model\Agent;
use App\Model\IO\IOInterface;
use App\Model\MCP\McpClient;
use App\Model\Tool\AgentTool;
use Exception;

class OrchestrateAgent
{
    private string $mcpJsonConfig;

    /**
     * Loads JSON configuration from a file.
     *
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    private function loadJsonConfig(string $filePath): string
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
     * @param Agent[] $agents
     * @param string $configFilePath
     * @throws Exception
     */
    public function __construct(private readonly array $agents = [], private readonly IOInterface $io, string $configFilePath = '')
    {
        if (empty($configFilePath) && isset($_ENV['AGENT_CONFIG_DIR'])) {
            $configFilePath = $_ENV['AGENT_CONFIG_DIR'] . '/orchestrate_agent.json';
        }

        try {
            $this->mcpJsonConfig = $this->loadJsonConfig($configFilePath);
        } catch (Exception $e) {
            // Handle exception or rethrow
            throw new Exception("Failed to load configuration: " . $e->getMessage(), 0, $e);
        }
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

    /**
     * Gets MCP clients from the JSON configuration.
     *
     * @return array
     * @throws Exception
     */
    public function getMcps(): array
    {
        $config = json_decode($this->mcpJsonConfig, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON configuration: " . json_last_error_msg());
        }

        if (!isset($config['mcpServers'])) {
            throw new Exception("Missing 'mcpServers' in configuration");
        }

        return McpClient::fromJsonConfig($this->mcpJsonConfig);
    }
}
