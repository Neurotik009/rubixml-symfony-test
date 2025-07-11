<?php

namespace App\Controller;

use App\Command\ChatbotTrainCommand;
use App\Service\ChatbotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ChatbotController extends AbstractController
{
    public function __construct(
        private readonly ChatbotTrainCommand $chatbotTrainCommand,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Chatbot Main Route
     *
     * @return Response
     */
    #[Route('/chatbot', name: 'app_chatbot', methods: ['GET'])]
    public function index(): Response
    {
        if(!in_array('view_chatbot', $this->getUser()->getPermissions())) {
            return $this->redirectToRoute('app_home');
        }
        return $this->render('chatbot/index.html.twig', [
            'controller_name' => 'ChatbotController',
        ]);
    }

    /**
     *
     *
     * @return Response
     */
    #[Route('/chatbot/predict', name: 'app_chatbot_predict', methods: ['GET'])]
    public function predict(): Response
    {
        if(!in_array('predict_chatbot', $this->getUser()->getPermissions())) {
            return $this->redirectToRoute('app_home');
        }
        return $this->render('chatbot/predict.html.twig');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/chatbot/predict', name: 'api_post_chatbot_predict', methods: ['POST'])]
    public function sendPredict(Request $request): JsonResponse
    {
        if(!in_array('predict_chatbot', $this->getUser()->getPermissions())) {
            return new JsonResponse(['error' => 'You are not allowed to access this route.']);
        }
        $validator = new ChatbotService($this->entityManager);

        $content = $request->getContent();
        $data = json_decode($content, true);

        $predictions = $validator->predict($data['question']);
        $mostLikelyAnswer = $predictions['predictions'][0];

        $answers = $this->entityManager->getRepository('App\Entity\ChatbotAnswer')->findBy(['label' => $mostLikelyAnswer]);

        return new JsonResponse(
            [
                'predictions' => $predictions,
                'answer' => $answers[0]->getAnswer(),
            ]
        );
    }

    /**
     * @return Response
     */
    #[Route('/chatbot/validate', name: 'app_chatbot_validate', methods: ['GET'])]
    public function validate(): Response
    {
        if(!in_array('validate_chatbot', $this->getUser()->getPermissions())) {
            return $this->redirectToRoute('app_home');
        }
        $validator = new ChatbotService($this->entityManager);
        return $this->render('chatbot/validate.html.twig', ['validate' => $validator->validate()]);
    }

    /**
     * @param KernelInterface $kernel
     * @return Response
     */
    #[Route('/chatbot/train', name: 'app_chatbot_train', methods: ['GET'])]
    public function train(KernelInterface $kernel): Response
    {
        if(!in_array('train_chatbot', $this->getUser()->getPermissions())) {
            return $this->redirectToRoute('app_home');
        }
        // Initialize Application
        $kernel->boot();
        $application = new Application();
        $application->setAutoExit(false);

        $application->add($this->chatbotTrainCommand);

        $input = new ArrayInput([
            'command' => 'chatbot:train',
            'rebuild' => 1,
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

    /**
     * @param KernelInterface $kernel
     * @param $rebuild
     * @return Response
     */
    #[Route('/chatbot/train/{rebuild}/{filename}', name: 'app_chatbot_add_train', methods: ['GET'])]
    public function addTrain(KernelInterface $kernel, $rebuild): Response
    {
        if(!in_array('train_chatbot', $this->getUser()->getPermissions())) {
            return $this->redirectToRoute('app_home');
        }
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
}
