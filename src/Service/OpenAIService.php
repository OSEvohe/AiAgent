<?php

namespace App\Service;

use App\Model\AIMessage;
use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\StreamResponse;

class OpenAIService implements OpenAIServiceInterface
{
    private Client $client;
    private string $selectedModel = '';

    public function __construct()
    {
        $this->client = OpenAI::factory()
            ->withBaseUri('http://192.168.192.1:1234/v1')
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

    public function
    sendToLlm(?AIMessage $message, array $history = [], bool $stream = false): CreateResponse|StreamResponse
    {
        $params = $message ? $message->toArray() : [];
        $model = $this->selectedModel;

        $messages = $history;
        if ($message && !empty($params['input'])) {
            $messages[] = ['role' => 'user', 'content' => $params['input']];
        }

        $chatParams = [
            'model' => $model,
            'messages' => $messages,
        ];

        if (isset($params['temperature'])) {
            $chatParams['temperature'] = $params['temperature'];
        }

        if (isset($params['max_output_tokens'])) {
            $chatParams['max_tokens'] = $params['max_output_tokens'];
        }

        if (isset($params['tools'])) {
            $chatParams['tools'] = $params['tools'];
        }

        if ($stream) {
            $chatParams['stream'] = true;
            return $this->client->chat()->createStreamed($chatParams);
        }

        //dump($chatParams); // For debugging purposes, remove in production
        return $this->client->chat()->create($chatParams);
    }
}
