<?php

namespace App\Model\Core\Provider;

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
     * Sends a message to the LLM and returns the response
     *
     * @param array<string, mixed> $context
     * @return CreateResponse|StreamResponse
     */
    public function sendToLlm(array $context= []): CreateResponse|StreamResponse;
}
