<?php

namespace App\Controller;

use App\Service\TrainChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Polyfill\Intl\Icu\DateFormat\Transformer;

final class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'app_chatbot')]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig', [
            'controller_name' => 'ChatbotController',
        ]);
    }

    #[Route('/chatbot/predict', name: 'app_chatbot_predict')]
    public function predict(): Response
    {
        return $this->render('chatbot/predict.html.twig');
    }

    #[Route('/chatbot/validate', name: 'app_chatbot_predict')]
    public function validate(): Response
    {
        $validator = new TrainChatbotService();
        return $this->render('chatbot/validate.html.twig', ['validate' => $validator->validate()]);
    }
}
