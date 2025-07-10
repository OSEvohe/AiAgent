<?php

namespace App\Model\Agent;

interface Agent
{
    public function getName(): string;

    public function getDescription(): string;

    public function getTools(): array;

    public function getMcps(): array;
}
