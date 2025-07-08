<?php

namespace App\Service;

use App\Model\AIMessage;
use App\Model\AITool;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;
use Symfony\Component\Console\Style\SymfonyStyle;

interface ToolServiceInterface
{
    /**
     * @param AITool[] $tools
     */
    public function processLlmResponse(
        CreateResponse|StreamResponse $response,
        array &$history,
        AIMessage $message,
        array $tools,
        ?SymfonyStyle $io = null
    ): string;
}
