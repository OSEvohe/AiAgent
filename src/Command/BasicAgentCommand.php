<?php

namespace App\Command;

use App\Model\Discussion;
use App\Model\IO\Terminal;
use App\Model\MCP\Jetbrains;
use App\Service\OpenAIService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'basic-agent',
    description: 'Add a short description for your command',
)]
class BasicAgentCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::OPTIONAL, 'Text Input sent to LLM');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $prompt = $input->getArgument('input');

        if (!$prompt) {
            $io->note('No input provided. Please provide a prompt as an argument.');
            return Command::FAILURE;
        }

        $discussion = new Discussion(
            openAIService: new OpenAIService($_ENV['LLM_URL'] . $_ENV['LLM_ENDPOINT']) ,
            model: '',
            io: new Terminal($output),
            tools: [],
            mcps: [new Jetbrains()],
        );

        $discussion->sendUserMessage($prompt);

        //dump($discussion->getContext());

        return Command::SUCCESS;
    }
}
