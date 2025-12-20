<?php

namespace App\Controller;

use App\Entity\ClientSession;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\HistoryService;
use App\Service\MessagePreparationService;
use App\Service\OperatorChatService;
use App\Service\SessionService;
use App\Service\TopicService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ApiController extends AbstractController
{
    public function __construct(
        private readonly TopicService $topicService,
        private readonly MessagePreparationService $messagePreparationService,
        private readonly SessionService $sessionService,
        private readonly HistoryService $historyService,
        private readonly OperatorChatService $chatService,
        private readonly MessageRepository $messageRepository,
        private readonly ManagerRegistry $doctrine,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/get-user', name: 'app_get_user_id', methods: ['GET'])]
    public function getUserId(): JsonResponse
    {
        return $this->json([
            'userId' => $this->sessionService->getUserId(),
        ]);
    }

    #[Route('/chat', name: 'api_chat', methods: ['POST', 'OPTIONS'])]
    public function chatSession(
        Request $request
    ): JsonResponse {
        // JSON input
        $input = json_decode($request->getContent(), true) ?: [];
        $messageText = trim($input['message'] ?? '');

        if ('' === $messageText) {
            return $this->json([
                'ok' => false,
                'error' => 'Empty message',
            ], 400);
        }

        // 1. Получаем / создаём клиентскую сессию (как в index)
        $session = $this->chatService->getOrCreateClientSession();
        $messages = $this->messageRepository->findMessagesForSession($session->getId());

        $userId = $this->sessionService->getUserId();
        $session = $this->doctrine
            ->getRepository(ClientSession::class)
            ->findOneBy(['externalId' => $userId]);

        // $sessionStatus = $session->getStatus();

        // 5. Возвращаем JSON для виджета
        return $this->json([
            'ok' => true,
            'response' => $messages,
            'sessionId' => $session->getExternalId(),
        ]);
    }

    #[Route('/chat/message', name: 'app_chat_message', methods: ['POST', 'OPTIONS'])]
    public function sendMessage(
        Request $request,
        LoggerInterface $logger,
    ): JsonResponse {
        $logger->info('📨 Получен запрос на /chat/message', [
            'method' => $request->getMethod(),
            'content_type' => $request->headers->get('Content-Type'),
        ]);

        if ('OPTIONS' === $request->getMethod()) {
            $logger->debug('🔄 Обработан OPTIONS запрос');

            return new JsonResponse(null, 204);
        }

        try {
            $input = $request->toArray();
            //          $message = $request->request->get('message');
            $message = $input['message'] ?? null;

            //            $logger->debug('📥 Данные запроса', [
            //                'message_from_request' => $message,
            //                'input_array' => $input,
            //                'raw_body' => $request->getContent(),
            //            ]);

            if (empty($message)) {
                $logger->warning('❌ Пустое сообщение', ['input' => $input]);

                return new JsonResponse(
                    ['error' => 'Сообщение не может быть пустым'],
                    400
                );
            }

            // $logger->info('🔍 Проверка темы сообщения', ['message' => $message]);
            if ($this->topicService->isForbidden($message)) {
                // $logger->warning('🚫 Запрещенная тема', ['message' => $message]);

                return new JsonResponse(
                    ['error' => 'Данная тема запрещена'],
                    403
                );
            }

            // $logger->info('⚙️ Подготовка ответа', ['message' => $message]);
            $response = $this->messagePreparationService->prepare($message);

            //            $logger->info('✅ Успешный ответ', [
            //                'original_message' => $message,
            //                'response_length' => strlen($response[0]['text'] ?? ''),
            //            ]);

            return new JsonResponse([
                'response' => $response[0]['text'] ?? 'Не удалось сгенерировать ответ',
            ]);
        } catch (\Throwable $e) {
            $logger->error('💥 Критическая ошибка', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new JsonResponse(
                ['error' => 'Внутренняя ошибка сервера'],
                500
            );
        }
    }

    #[Route('/clear-history', name: 'app_clear_history', methods: ['POST', 'OPTIONS'])]
    public function clearHistory(
        Request $request,
        LoggerInterface $logger,
    ): JsonResponse {
        // Clear history
        $userId = $this->sessionService->getUserId();
        $this->historyService->clearHistory($userId);

        // Close session
        // $this->sessionService->closeSession($userId);

        // Clear session Внимание, этот метод удаляет записи сообщений, Cascade remove
        // $this->sessionService->clearSession($userId);

        return new JsonResponse(null, 204);
    }

    #[Route('/close-session', name: 'app_clear_session', methods: ['POST'])]
    public function closeSession(): JsonResponse
    {
        $userId = $this->sessionService->getUserId();

        $this->sessionService->closeSession($userId);

        return new JsonResponse(null, 204);
    }

    // #[IsGranted('ROLE_ADMIN or ROLE_EDITOR')]
    #[Route('/admin-close-session', name: 'app_admin_clear_session', methods: ['POST'])]
    public function adminCloseSession(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $session = $data['session'] ?? null;

        if (!$session) {
            return new JsonResponse(['error' => 'Session ID missing'], 400);
        }
        $this->sessionService->closeSessionByAdmin($session);

        return new JsonResponse(null, 204);
    }

    #[Route('/open-session', name: 'app_open_session', methods: ['POST'])]
    public function openSession(): JsonResponse
    {
        $userId = $this->sessionService->getUserId();
        $this->sessionService->openSession($userId);

        return new JsonResponse(null, 204);
    }

    #[Route('/get-operator-messages', name: 'api_operator_messages')]
    public function getMessages(): JsonResponse
    {
        $session = $this->chatService->getClientSession();
        if (!$session) {
            return new JsonResponse(['error' => 'Session not found'], 400);
        }

        // Получаем только операторские сообщения
        $messages = $this->messageRepository->findOperatorMessagesForSession($session->getId());

        return new JsonResponse([
            'messages' => array_map(function ($msg) {
                return [
                    'text' => $msg->getMessage(),
                    'time' => $msg->getCreatedAt()->format('Y-m-d H:i:s'),
                ];
            }, $messages),
        ], 200);
    }

    #[Route('/session/set-client-data', methods: ['POST'])]
    public function setClientData(
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;

        if (!$name || !$phone) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }
        $clientSession = $this->chatService->getOrCreateClientSession();

        if (!$clientSession) {
            return new JsonResponse(['error' => 'No client session'], 400);
        }

        if (!$clientSession) {
            return new JsonResponse(['error' => 'Session not found'], 404);
        }

        // обновляем
        $clientSession->setName($name);
        $clientSession->setPhone($phone);

        $em = $this->doctrine->getManager();
        $em->persist($clientSession);
        $em->flush();

        // $sessionRepo->save($clientSession, true);

        return new JsonResponse(['status' => 'ok']);
    }
}
