<?php

namespace App\Model\IO;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class Terminal implements IOInterface
{
    public function __construct(private SymfonyStyle $io)
    {
    }

    public function output(string $message): void
    {
        $this->io->writeln($message);
    }

    public function input(string $prompt): string
    {
        return $this->io->ask($prompt);
    }
}
