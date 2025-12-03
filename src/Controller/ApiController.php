<?php

namespace App\Controller;

use App\Entity\Message;
use App\Enum\MessageRole;
use App\Repository\MessageRepository;
use App\Service\HistoryService;
use App\Service\MessagePreparationService;
use App\Service\OperatorChatService;
use App\Service\SessionService;
use App\Service\TopicService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    public function __construct(
        private readonly TopicService $topicService,
        private readonly MessagePreparationService $messagePreparationService,
        private readonly SessionService $sessionService,
        private readonly HistoryService $historyService,
        private readonly OperatorChatService $chatService,
    ) {
    }

    #[Route('/get-user', name: 'app_get_user_id', methods: ['GET'])]
    public function getUserId(): JsonResponse
    {
        return $this->json([
            'userId' => $this->sessionService->getUserId(),
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

            $logger->debug('📥 Данные запроса', [
                'message_from_request' => $message,
                'input_array' => $input,
                'raw_body' => $request->getContent(),
            ]);

            if (empty($message)) {
                $logger->warning('❌ Пустое сообщение', ['input' => $input]);

                return new JsonResponse(
                    ['error' => 'Сообщение не может быть пустым'],
                    400
                );
            }

            $logger->info('🔍 Проверка темы сообщения', ['message' => $message]);
            if ($this->topicService->isForbidden($message)) {
                $logger->warning('🚫 Запрещенная тема', ['message' => $message]);

                return new JsonResponse(
                    ['error' => 'Данная тема запрещена'],
                    403
                );
            }

            $logger->info('⚙️ Подготовка ответа', ['message' => $message]);
            $response = $this->messagePreparationService->prepare($message);

            $logger->info('✅ Успешный ответ', [
                'original_message' => $message,
                'response_length' => strlen($response[0]['text'] ?? ''),
            ]);

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

    #[Route('/clear-session', name: 'app_clear_session', methods: ['POST'])]
    public function clearSession(): JsonResponse
    {
        $userId = $this->sessionService->getUserId();
        $this->sessionService->closeSession($userId);

        return new JsonResponse(null, 204);
    }

    // ###################################################################################################################

    #[Route('/messages', name: 'api_messages')]
    public function messages(
        MessageRepository $repo
    ): JsonResponse {
        $session = $this->chatService->getOrCreateClientSession();
        $messages = $repo->findMessagesForSession($session->getId());

        return $this->json([
            'messages' => array_map(fn ($m) => [
                'id' => $m->getId(),
                'role' => $m->getOperator() ? 'operator' : 'user',
                'content' => $m->getMessage(),
                'time' => $m->getCreatedAt()->format('c'),
            ], $messages),
        ]);
    }

    #[Route('/send', name: 'api_send', methods: ['POST'])]
    public function send(
        Request $request,
        MessagePreparationService $prep,
        TopicService $topics,
        OperatorChatService $chatService,
        EntityManagerInterface $em
    ): JsonResponse {
        $session = $chatService->getOrCreateClientSession();
        $data = json_decode($request->getContent(), true);
        $text = $data['message'] ?? '';

        // сохраняем сообщение пользователя
        $msg = new Message();
        $msg->setClientSession($session);
        $msg->setMessage($text);
        $msg->setRole(MessageRole::CLIENT);
        $msg->setCreatedAt(new \DateTimeImmutable());
        $em->persist($msg);
        $em->flush();

        // ответ бота
        // $responseText = $topics->generateAnswer($text);
        $responseText = 'Вы написали: '.$text;

        // сохраняем ответ бота
        $reply = new Message();
        $reply->setClientSession($session);
        $reply->setMessage($responseText);
        $reply->setRole(MessageRole::SYSTEM);
        $reply->setCreatedAt(new \DateTimeImmutable());
        $em->persist($reply);
        $em->flush();

        return $this->json(['response' => $responseText]);
    }
}
