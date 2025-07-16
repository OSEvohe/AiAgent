<?php

namespace App\Model\Core\Tool;

use App\Model\Core\IOInterface;
use App\Model\Core\Mcp\McpClient;
use App\Model\Core\Mcp\McpTool;
use App\Model\Core\Message\ToolResultResponse;
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
                    $this->io?->output("Running tool: " . $tool->getName());
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
                if ($tool instanceof AgentTool) {
                    $this->io?->output("Running AgentRunner: {$tool->getName()} with task:  {$toolCall->function->arguments}");
                } else {
                    $this->io?->output("Running tool: {$tool->getName()}");
                }
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
