<?php

namespace App\Model\Tool;

use App\Model\IO\IOInterface;
use OpenAI\Responses\Chat\CreateResponseToolCall;

class ToolsHandler
{
    public function __construct(
        /** @var AITool[] */
        private readonly array $tools = [],
        private readonly ?IOInterface $io = null,
    )
    {
    }

    /**
     * @param CreateResponseToolCall[] $toolCalls
     */
    public function handleToolCalls(array $toolCalls): array
    {
        $resultCalls = [];

        foreach ($toolCalls as $toolCall) {
            foreach ($this->tools as $tool) {
                if ($tool->getName() === $toolCall->function->name) {
                    $this->io?->output("Running tool: {$tool->getName()} with arguments: {$toolCall->function->arguments}");
                }

                $resultCalls[] = $tool->execute($toolCall);
            }
        }
        return $resultCalls;
    }



}
