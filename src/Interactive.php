<?php

namespace App;

use Clue\React\Stdio\Stdio;

class Interactive
{
    private Stdio $stdio;

    public function __construct()
    {
        $this->stdio = new Stdio();
        $this->initialize();
    }

    private function initialize(): void
    {
        $this->stdio->setPrompt('> ');

        // Limit history to HISTSIZE env
        $limit = getenv('HISTSIZE');
        if ($limit === '' || $limit < 0) {
            // Empty string or negative value means unlimited
            $this->stdio->limitHistory(null);
        } elseif ($limit !== false) {
            // Apply any other value if given
            $this->stdio->limitHistory($limit);
        }

        // Autocomplete the following commands (at offset=0/1 only)
        $this->stdio->setAutocomplete(function ($_, $offset) {
            return $offset > 1 ? [] : ['exit', 'quit', 'help', 'echo', 'print', 'printf'];
        });

        $this->stdio->write('Welcome to this interactive demo' . PHP_EOL);

        // React to commands the user entered
        $this->stdio->on('data', function ($line) {
            $this->handleInput($line);
        });
    }

    private function handleInput(string $line): void
    {
        $line = rtrim($line, "\r\n");

        // Add all lines from input to history
        // Skip empty line and duplicate of previous line
        $all = $this->stdio->listHistory();
        if ($line !== '' && $line !== end($all)) {
            $this->stdio->addHistory($line);
        }

        $this->stdio->write('you just said: ' . $line . ' (' . strlen($line) . ')' . PHP_EOL);

        if (in_array(trim($line), ['quit', 'exit'])) {
            $this->stdio->end();
        }
    }

    public function run(): void
    {
        while (true) {
            // Keep the event loop running
           sleep(1);
        }
    }
}
