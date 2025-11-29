<?php

namespace App\Controller;

use App\Service\MessagePreparationService;
use App\Service\SessionService;
use App\Service\TopicService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly TopicService $topicService,
        private readonly MessagePreparationService $messagePreparationService,
        private readonly SessionService $sessionService,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(
    ): Response {
        return $this->render('page/index.html.twig', [
        ]);
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
}
