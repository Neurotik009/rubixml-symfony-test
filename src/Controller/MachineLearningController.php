<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class for training and testing the ml-model
 */
final class MachineLearningController extends AbstractController
{
    #[Route('/machine_learning', name: 'app_machine_learning')]
    public function index(): Response
    {
        return $this->render('machine_learning/index.html.twig', [
            'controller_name' => 'MachineLearningController',
        ]);
    }


    #[Route('/machine_learning/train/{rebuild}', name: 'app_chatbot_train')]
    public function train(KernelInterface $kernel, $rebuild): Response
    {
        // Initialize Application
        $kernel->boot();
        $application = new Application();
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'chatbot:train',
            'rebuild' => $rebuild,
        ]);

        // Set OutputBuffer and run command
        $output = new BufferedOutput();
        try {
            $application->run($input, $output);

            return new Response($output->fetch());
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }

    #[Route('/machine_learning/train/{rebuiild}/{filename}', name: 'app_chatbot_add_train')]
    public function addTrain(KernelInterface $kernel, $rebuild): Response
    {
        // Initialize Application
        $kernel->boot();
        $application = new Application();
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'chatbot:train',
            '--rebuild' => $rebuild,
        ]);

        // Set OutputBuffer and run command
        $output = new BufferedOutput();
        try {
            $application->run($input, $output);

            return new Response($output->fetch());
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }



    #[Route('/machine_learning/predict', name: 'app_chatbot_predict')]
    public function predict(): Response
    {
        return $this->render('chatbot/predict.html.twig', []);
    }
}
