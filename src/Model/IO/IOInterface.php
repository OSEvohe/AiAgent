<?php

namespace App\Model\IO;

interface IOInterface
{
    public function output(string $message): void;
}
