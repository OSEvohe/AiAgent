<?php

namespace App\Model\Core\Message;

class SystemMessage
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
            'role' => 'system',
            'content' => $this->input,
        ];
    }
}

