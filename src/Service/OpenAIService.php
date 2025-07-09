<?php

namespace App\Service;

use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class OpenAIService implements OpenAIServiceInterface
{
    private Client $client;
    private string $selectedModel = '';

    public function __construct(string $baseUri = 'http://127.0.0.1:1234/v1')
    {
        $this->client = OpenAI::factory()
            ->withBaseUri($baseUri)
            ->make();
    }

    /**
     * Set the base URI for the OpenAI client
     *
     * @param string $baseUri
     */
    public function setBaseUri(string $baseUri): void
    {
        // Create a new client with the updated base URI
        $this->client = OpenAI::factory()
            ->withBaseUri($baseUri)
            ->make();
    }

    /**
     * Returns the OpenAI client instance
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Retrieves available models
     *
     * @return array
     */
    public function getModels(): array
    {
        $response = $this->client->models()->list();
        return $response->toArray()['data'] ?? [];
    }

    /**
     * Selects a specific model for use
     *
     * @param string $modelId
     * @return void
     */
    public function selectModel(string $modelId): void
    {
        $this->selectedModel = $modelId;
    }

    public function sendToLlm(array $context = []): CreateResponse|StreamResponse
    {
        return $this->client->chat()->create($context);
    }
}
