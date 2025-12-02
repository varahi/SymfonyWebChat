<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Service\OperatorChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly OperatorChatService $chatService,
        private MessageRepository $messageRepository,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(
    ): Response {
        $session = $this->chatService->getOrCreateClientSession();
        $messages = $this->messageRepository->findMessagesForSession($session->getId());

        return $this->render('page/index.html.twig', [
            'messages' => $messages,
        ]);
    }
}
