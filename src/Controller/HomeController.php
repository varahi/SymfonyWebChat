<?php

namespace App\Controller;

use App\Entity\ClientSession;
use App\Repository\MessageRepository;
use App\Service\OperatorChatService;
use App\Service\SessionService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly OperatorChatService $chatService,
        private MessageRepository $messageRepository,
        private readonly SessionService $sessionService,
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(
    ): Response {

        throw $this->createNotFoundException();

//        $session = $this->chatService->getOrCreateClientSession();
//        $messages = $this->messageRepository->findMessagesForSession($session->getId());
//
//        $userId = $this->sessionService->getUserId();
//        $session = $this->doctrine
//            ->getRepository(ClientSession::class)
//            ->findOneBy(['externalId' => $userId]);
//
//        // $sessionClosed = null !== $session?->getClosedAt(); // bool
//        $sessionStatus = $session->getStatus();
//
//        return $this->render('page/index.html.twig', [
//            'messages' => $messages,
//            'sessionStatus' => $sessionStatus,
//        ]);
    }

    #[Route('/chat-embed', name: 'chat_embed')]
    public function embed(
        Request $request,
    ): Response
    {
        if ($request->headers->get('sec-fetch-dest') !== 'iframe') {
            return new Response('Forbidden', 403);
        }

        $session = $this->chatService->getOrCreateClientSession();
        $messages = $this->messageRepository->findMessagesForSession($session->getId());

        $userId = $this->sessionService->getUserId();
        $session = $this->doctrine
            ->getRepository(ClientSession::class)
            ->findOneBy(['externalId' => $userId]);

        $sessionStatus = $session->getStatus();

        return $this->render('page/embed.html.twig', [
            'messages' => $messages,
            'sessionStatus' => $sessionStatus,
        ]);
    }
}
