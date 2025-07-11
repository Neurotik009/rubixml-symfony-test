<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ChatbotAnswer;

class QuestionService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveAnswer(ChatbotAnswer $answer): void
    {
        $this->entityManager->persist($answer);
        $this->entityManager->flush();
    }

}
