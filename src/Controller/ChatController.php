<?php

namespace App\Controller;

use App\Entity\ClientSession;
use App\Entity\Message;
use App\Enum\MessageRole;
use App\Enum\MessageStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/client-session')]
class ChatController extends AbstractController
{
    #[Route('/{id}/chat', name: 'admin_chat', methods: ['GET'])]
    public function chat(ClientSession $session): Response
    {
        return $this->render('admin/chat/chat.html.twig', [
            'session' => $session,
            'messages' => $session->getMessages(),
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

        $message = new Message();
        $message->setClientSession($session);
        $message->setMessage($text);
        $message->setRole(MessageRole::OPERATOR);
        $message->setStatus(MessageStatus::PROCESSED);
        $message->setOperator($this->getUser());
        $message->setCreatedAt(new \DateTimeImmutable());

        $em->persist($message);
        $em->flush();

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
