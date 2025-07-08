<?php

namespace App\Service;

use App\Model\AIMessage;
use App\Model\AITool;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;
use Symfony\Component\Console\Style\SymfonyStyle;

class ToolService implements ToolServiceInterface
{
    public function __construct(private readonly OpenAIServiceInterface $openAIService)
    {
    }

    /**
     * @param AITool[] $tools
     */
    public function processLlmResponse(
        CreateResponse|StreamResponse $response,
        array &$history,
        AIMessage $message,
        array $tools,
        ?SymfonyStyle $io = null
    ): string {
        if ($response instanceof StreamResponse) {
            if ($io === null) {
                throw new \InvalidArgumentException('A SymfonyStyle $io object is required for streaming.');
            }

            $io->write('Assistant: ');
            $finalResponseText = '';
            $toolCalls = [];

            foreach ($response as $chunk) {
                $delta = $chunk->choices[0]->delta;
                $content = $delta->content;
                if ($content !== null) {
                    $finalResponseText .= $content;
                    $io->write($content);
                }

                if (!empty($delta->toolCalls)) {
                    foreach ($delta->toolCalls as $toolCall) {
                        if (!isset($toolCalls[$toolCall->index])) {
                            $toolCalls[$toolCall->index] = ['id' => '','type' => '', 'function' => ['name' => '', 'arguments' => '']];
                        }
                        if (isset($toolCall->id)) {
                            $toolCalls[$toolCall->index]['id'] = $toolCall->id;
                        }

                        if (isset($toolCall->type)) {
                            $toolCalls[$toolCall->index]['type'] = $toolCall->type;
                        }

                        if (isset($toolCall->function->name)) {
                            $toolCalls[$toolCall->index]['function']['name'] = $toolCall->function->name;
                        }
                        if (isset($toolCall->function->arguments)) {
                            $toolCalls[$toolCall->index]['function']['arguments'] .= $toolCall->function->arguments;
                        }
                    }
                }

                if ($chunk->choices[0]->finishReason === 'tool_calls') {
                    $io->newLine();
                    return $this->handleToolCalls($toolCalls, $tools, $history, $io);
                }

            }
            $io->newLine();

            return $finalResponseText;
        }

        $choice = $response->choices[0];

        if (!empty($choice->message->toolCalls)) {
            return $this->handleToolCalls($choice->message->toolCalls, $tools, $history, $io);
        }

        return $choice->message->content ?? '';
    }

    /**
     * @param array $toolCalls
     * @param AITool[] $tools
     * @param array $history
     * @param SymfonyStyle|null $io
     * @return string
     */
    private function handleToolCalls(array $toolCalls, array $tools, array &$history, ?SymfonyStyle $io): string
    {
        $io?->note('Tool call detected.');
        $toolCall = $toolCalls[0];
        $toolName = is_array($toolCall) ? $toolCall['function']['name'] : $toolCall->function->name;
        $toolArguments = is_array($toolCall) ? json_decode($toolCall['function']['arguments'], true) : json_decode($toolCall->function->arguments, true);

        $toolFound = false;
        foreach ($tools as $tool) {
            if ($tool->getName() === $toolName) {
                $toolFound = true;
                $io?->note(sprintf('Executing tool: %s', $toolName));
                $toolResult = $tool->execute($toolArguments);

                $toolCallArray = is_array($toolCall) ? $toolCall : $toolCall->toArray();
                $history[] = ['role' => 'assistant', 'content' => null, 'tool_calls' => [$toolCallArray]];
                $toolCallId = $toolCallArray['id'];
                $history[] = ['role' => 'tool', 'tool_call_id' => $toolCallId, 'name' => $toolName, 'content' => json_encode($toolResult)];

                $newResponse = $this->openAIService->sendToLlm(new AIMessage(''), $history);
                return $newResponse->choices[0]->message->content ?? '';
            }
        }

        if (!$toolFound) {
            $io?->warning(sprintf('Tool "%s" not found.', $toolName));
        }

        return '';
    }
}
