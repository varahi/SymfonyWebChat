<?php

namespace App\Controller;

use App\Service\MessagePreparationService;
use App\Service\TopicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly TopicService $topicService,
        private readonly MessagePreparationService $messagePreparationService
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(
    ): Response {
        return $this->render('page/index.html.twig', [
        ]);
    }

    #[Route('/chat/message', name: 'app_chat_message', methods: ['POST', 'OPTIONS'])]
    public function sendMessage(
        Request $request,
    ): JsonResponse {

        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse(null, 204);
        }

        $message = $request->request->get('message');
        $input = $request->toArray();

        try {
            if (empty($message)) {
                return new JsonResponse(
                    ['error' => 'Сообщение не может быть пустым'],
                    400
                );
            }
            if ($this->topicService->isForbidden($message)) {
                return new JsonResponse(
                    ['error' => 'Данная тема запрещена'],
                    403
                );
            }

            $response = $this->messagePreparationService->prepare($message);
            return new JsonResponse([
                'response' => $response[0]['text'] ?? 'Не удалось сгенерировать ответ'
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse(
                ['error' => 'Внутренняя ошибка сервера'],
                500
            );
        }

    }
}
