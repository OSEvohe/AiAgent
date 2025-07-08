<?php

namespace App\Service;

use App\Model\UserMessage;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

interface OpenAIServiceInterface
{
    /**
     * Returns the OpenAI client instance
     *
     * @return Client
     */
    public function getClient(): Client;

    /**
     * Retrieves available models
     *
     * @return array
     */
    public function getModels(): array;

    /**
     * Selects a specific model for use
     *
     * @param string $modelId
     * @return void
     */
    public function selectModel(string $modelId): void;

    /**
     * Sends a message to the LLM and returns the response
     *
     * @param array $context
     * @param bool $stream
     * @return CreateResponse|StreamResponse
     */
    public function sendToLlm(array $context= []): CreateResponse|StreamResponse;
}
