<?php

namespace App\Command;

use App\Model\Discussion;
use App\Model\IO\Terminal;
use App\Model\MCP\Jetbrains;
use App\Model\MCP\ServerFileSystem;
use App\Model\Tool\FileSystemTool;
use App\Model\Tool\TestTool;
use App\Model\Tool\WeatherTool;
use App\Service\OpenAIServiceInterface;
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
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::OPTIONAL, 'Text Input sent to LLM')
            ->addOption('llm-url', null, InputOption::VALUE_OPTIONAL, 'URL of the LLM API endpoint');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check if a custom LLM URL was provided via command line option
        $llmUrlOption = $input->getOption('llm-url');
        if ($llmUrlOption) {
            $this->openAIService->setBaseUri($llmUrlOption);
        }

        $arg1 = $input->getArgument('input');

        if (!$arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
            return Command::FAILURE;
        }


        $discussion = new Discussion(
            openAIService: $this->openAIService,
            model: '',
            io: new Terminal($output),
            tools: [],
            mcps: [new Jetbrains()],
        );

        $discussion->sendUserMessage($arg1);

        //dump($discussion->getContext());

        return Command::SUCCESS;
    }
}
