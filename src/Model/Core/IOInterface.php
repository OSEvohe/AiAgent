<?php

namespace App\Model\Core;

interface IOInterface
{
    public function output(string $message): void;
}
