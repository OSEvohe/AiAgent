<?php

namespace App\Model\IO;

use Symfony\Component\Console\Output\OutputInterface;

readonly class Terminal implements IOInterface
{

    public function __construct(private OutputInterface $output)
    {
    }

    public function output(string $message): void
    {
        $this->output->writeln($message);
    }
}
