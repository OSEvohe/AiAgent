<?php

namespace App\Model\Core\Provider;

use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class OpenAIService implements OpenAIServiceInterface
{
    private Client $client;
    public function __construct(string $baseUri = 'http://127.0.0.1:1234/v1')
    {
        $this->client = OpenAI::factory()
            ->withBaseUri($baseUri)
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 1200]))
            ->make();
    }

    /**
     * Set the base URI for the OpenAI client
     *
     * @param string $baseUri
     */
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

    public function sendToLlm(array $context = []): CreateResponse|StreamResponse
    {
        return $this->client->chat()->create($context);
    }
}
