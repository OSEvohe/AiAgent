<?php

namespace App\Model\Tool;

use App\Model\IO\IOInterface;
use App\Model\MCP\McpClient;
use App\Model\MCP\McpTool;
use Exception;
use OpenAI\Responses\Chat\CreateResponseToolCall;

class ToolsHandler
{
    private bool $toolsEnabled = true;


    public function __construct(
        /** @var AITool[] */
        private array $tools = [],
        /** @var McpClient[] */
        private readonly array $mcps = [],
        private readonly ?IOInterface $io = null,
    ) {
        // add each tools provided by MCPs to the tools array
        foreach ($this->mcps as $mcp) {
            foreach ($mcp->listTools() as $tool) {
                $this->tools[] = new McpTool($tool, $mcp);
            }
        }
    }

    /**
     * @param CreateResponseToolCall[] $toolCalls
     */
    public function handleToolCalls(
        array $toolCalls
    ): array {
        $resultCalls = [];

        foreach ($toolCalls as $toolCall) {
            foreach ($this->tools as $tool) {
                if ($tool->getName() === $toolCall->function->name) {
                    $this->io?->output("Running tool: {$tool->getName()}");
                }

                $resultCalls[] = $tool->execute($toolCall);
            }
        }
        return $resultCalls;
    }

    /**
     * @throws Exception
     */
    public function handleSingleToolCall(
        CreateResponseToolCall $toolCall
    ): ToolResultResponse {
        foreach ($this->tools as $tool) {
            if ($tool->getName() === $toolCall->function->name) {
                $this->io?->output("Running tool: {$tool->getName()} with arguments: {$toolCall->function->arguments}");
                return $tool->execute($toolCall);
            }
        }
        throw new Exception("Tool not found: " . $toolCall->function->name);
    }

    public function getTools(): array
    {
        return $this->toolsEnabled ? $this->tools : [];
    }

    public function disableTools(): void
    {
        $this->toolsEnabled = false;
    }

    public function enableTools(): void
    {
        $this->toolsEnabled = true;
    }
}
