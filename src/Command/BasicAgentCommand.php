<?php

namespace App\Command;

use App\Model\AIMessage;
use App\Model\Tool\WeatherTool;
use App\Service\OpenAIServiceInterface;
use App\Service\ToolServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'basic-agent',
    description: 'Add a short description for your command',
)]
class BasicAgentCommand extends Command
{
    public function __construct(
        private readonly OpenAIServiceInterface $openAIService,
        private readonly ToolServiceInterface $toolService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
            ->addOption('stream', null, InputOption::VALUE_NONE, 'Enable streaming')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $arg1 = $input->getArgument('arg1');
        $stream = $input->getOption('stream');

        if ($arg1 === 'ls') {
            $this->listModels($io);
        } elseif ($arg1 === 'chat') {
            $this->chat($io, $stream);
        } elseif ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        return Command::SUCCESS;
    }

    private function chat(SymfonyStyle $io, bool $stream = false): void
    {
        $history = [];
        $tools = [new WeatherTool()];

        while (true) {
            $userInput = $io->ask('You');
            if ($userInput === '/quit' || $userInput === null) {
                break;
            }

            $message = new AIMessage($userInput);
            $message->setTools(array_map(fn($tool) => $tool->toArray(), $tools));

            $response = $this->openAIService->sendToLlm($message, $history, $stream);

            $responseText = $this->toolService->processLlmResponse($response, $history, $message, $tools, $io);


                $io->writeln('Assistant: ' . $responseText);


            $history[] = ['role' => 'user', 'content' => $userInput];
            $history[] = ['role' => 'assistant', 'content' => $responseText];

            //dump($history); // For debugging purposes, remove in production
        }
    }

    private function listModels(SymfonyStyle $io): void
    {
        $models = $this->openAIService->getModels();

        $io->title('Available OpenAI Models:');

        $modelList = [];
        foreach ($models as $model) {
            $modelList[] = $model['id'];
        }

        $io->listing($modelList);
    }
}
