<?php

namespace App\Command;

use AllowDynamicProperties;
use App\Service\ChatbotService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AllowDynamicProperties] #[AsCommand(
    name: 'chatbot:train',
    description: 'Trains the chatbot with new data.'

)]
class ChatbotTrainCommand extends Command
{
    public function __construct(
        ChatbotService $chatbotService
    ) {
        $this->chatbotService = $chatbotService;
        parent::__construct();
    }


    public function configure(): void
    {
        $this
        // possible arguments
            ->addArgument('rebuild', InputArgument::REQUIRED, 'If the training dataset should be rebuilt.')
            ->addArgument('file', InputArgument::OPTIONAL, 'The file to train on. Only applicable if rebuild is false.')
        // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to train chatbot with new data.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if($input->getArgument('rebuild') === 1) {
            $status = $this->chatbotService->rebuildDataset($output);
        } else {
            $status = $this->chatbotService->addToDataset($output, $input->getArgument('file'));
        }

        return $status ? Command::SUCCESS : Command::FAILURE;
    }
}
