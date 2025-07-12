<?php

namespace App\Model\Core\Message;

class UserMessage
{


    public function __construct(private string $input)
    {
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function toArray(): array
    {
        return [
            'role' => 'user',
            'content' => $this->input,
        ];
    }
}

