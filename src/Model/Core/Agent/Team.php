<?php

namespace App\Model\Core\Agent;

use App\Model\Core\IOInterface;

interface Team
{
    public function initialize(IOInterface $io): void;

    public function sendMessage(string $message);
}
