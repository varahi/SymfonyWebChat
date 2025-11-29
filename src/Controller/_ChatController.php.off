<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
//use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ChatController extends AbstractController
{
    public function __construct(
        private MessageRepository $messageRepository,
        private UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/chat', name: 'app_chat')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
//        return $this->render('chat/index.html.twig', [
//            'controller_name' => 'ChatController',
//        ]);

        $currentUser = $this->getUser();
        $users = $this->userRepository->findAllExcept($currentUser->getId());

        return $this->render('chat/index.html.twig', [
            'users' => $users,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/chat/{userId}', name: 'app_chat_with_user')]
    #[IsGranted('ROLE_USER')]
    public function chatWithUser(
        int $userId,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $receiver = $this->userRepository->find($userId);
        $users = $this->userRepository->findAllExcept($currentUser->getId());

        if (!$receiver) {
            throw $this->createNotFoundException('User not found');
        }

        // Получаем историю сообщений
        $messages = $this->messageRepository->findMessagesBetweenUsers(
            $currentUser->getId(),
            $userId
        );

        return $this->render('chat/chat.html.twig', [
            'users' => $users,
            'receiver' => $receiver,
            'messages' => $messages,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/chat/send-message', name: 'app_send_message', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function sendMessage(
        Request $request,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $receiverId = $request->request->get('receiver_id');
        $messageText = $request->request->get('message');

        $receiver = $this->userRepository->find($receiverId);

        if (!$receiver || !$messageText) {
            return $this->json(['success' => false, 'error' => 'Invalid data']);
        }

        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setMessage($messageText);
        $message->setStatus('sent');

        //$entityManager = $this->doctrine->getManager();
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'text' => $message->getMessage(),
                'sender' => $currentUser->getUsername(),
                'time' => $message->getCreatedAt()->format('H:i'),
            ]
        ]);
    }

    #[Route('/chat/get-messages/{userId}', name: 'app_get_messages')]
    #[IsGranted('ROLE_USER')]
    public function getMessages(
        int $userId,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $messages = $this->messageRepository->findMessagesBetweenUsers(
            $currentUser->getId(),
            $userId
        );

        $data = [];
        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'text' => $message->getMessage(),
                'sender' => $message->getSender()->getUsername(),
                'is_own' => $message->getSender()->getId() === $currentUser->getId(),
                'time' => $message->getCreatedAt()->format('H:i'),
            ];
        }

        return $this->json($data);
    }
}
