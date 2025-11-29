<?php

namespace App\Controller;

use App\Service\FaqService;
use App\Service\MessagePreparationService;
use App\Service\Product\ProductService;
use App\Service\TopicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly TopicService $topicService,
        private readonly MessagePreparationService $messagePreparationService,
        private readonly FaqService $faqService,
        private readonly ProductService $productService,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(
    ): Response {
        return $this->render('page/index.html.twig', [
        ]);
    }

    // JsonResponse

    #[Route('/chat/message', name: 'app_chat_message', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        // ChatBotFactory $chatBotFactory,
        // ChatRequestHandler $requestHandler,
        // ErrorHandler $errorHandler
    ): Response {
        dd($request->request->all());

        //        try {
        //            $input = $request->toArray();
        //            $bot = $chatBotFactory->create();
        //            $response = $requestHandler->handle($input);
        //
        //            return $this->json($response);
        //
        //        } catch (\Throwable $e) {
        //            $errorData = $errorHandler->handle($e);
        //            $statusCode = $errorData['code'] ?? Response::HTTP_INTERNAL_SERVER_ERROR;
        //
        //            return $this->json($errorData, $statusCode);
        //        }
    }
}
