<?php

namespace App\Controller;

use App\Entity\ClientSession;
use App\Entity\Message;
use App\Enum\MessageRole;
use App\Enum\MessageStatus;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/client-session')]
class OperatorChatController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly KernelInterface $kernel,
        private readonly SessionService $sessionService,
    ) {
    }

    #[Route('/{id}/chat', name: 'admin_chat', methods: ['GET'])]
    public function chat(ClientSession $session): Response
    {
        $sessionStatus = $session->getStatus();

        if ('dev' === $this->kernel->getEnvironment()) {
            $this->logger->warning('Get session by method admin_chat', [
                'session' => $session->getId(),
            ]);
        }

        return $this->render('admin/chat/chat.html.twig', [
            'session' => $session,
            'messages' => $session->getMessages(),
            'sessionStatus' => $sessionStatus,
        ]);
    }

    #[Route('/{id}/reply', name: 'admin_chat_reply', methods: ['POST'])]
    public function reply(
        Request $request,
        ClientSession $session,
        EntityManagerInterface $em
    ): JsonResponse {
        $text = $request->request->get('text');

        if (!$text) {
            return new JsonResponse(['error' => 'Empty message'], 400);
        }

        if ('dev' === $this->kernel->getEnvironment()) {
            $this->logger->warning('Get session by method admin_chat_reply', [
                'session' => $session->getId(),
            ]);
        }

        $this->sessionService->operatorStartedSession($session->getId());

        $message = new Message();
        $message->setClientSession($session);
        $message->setMessage($text);
        $message->setRole(MessageRole::OPERATOR);
        $message->setStatus(MessageStatus::PROCESSED);
        $message->setOperator($this->getUser());
        $message->setCreatedAt(new \DateTimeImmutable());

        $em->persist($message);
        $em->flush();

        // ToDo: use websocket
        // Event for Reverb
        // dispatch(new \App\MessageEvent\MessageCreated($message));

        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/{id}/poll', name: 'admin_chat_poll')]
    public function poll(ClientSession $session): JsonResponse
    {
        $messages = [];

        foreach ($session->getMessages() as $m) {
            $messages[] = [
                'role' => $m->getRole()->value,
                'text' => $m->getMessage(),
                'createdAt' => $m->getCreatedAt()->format('H:i'),
            ];
        }

        return new JsonResponse(['messages' => $messages]);
    }
}
