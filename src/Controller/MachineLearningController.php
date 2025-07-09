<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

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


    #[Route('/machine_learning/train/{rebuild}', name: 'app_ml_train')]
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

    #[Route('/machine_learning/train/{rebuiild}/{filename}', name: 'app_ml_add_train')]
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

    #[Route('/machine_learning/predict', name: 'api_chatbot_predict')]
    public function apiPredict(Request $request): JsonResponse
    {
        try {
            // Request-Daten als JSON empfangen
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                throw new \Exception('Ungültige JSON-Daten');
            }

            $predictionService = new ChatbotService();
            // Hier können Sie Ihre ML-Vorhersagelogik implementieren
            $prediction = $predictionService->predict($data);

            return new JsonResponse($prediction);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);

        }
    }
}
