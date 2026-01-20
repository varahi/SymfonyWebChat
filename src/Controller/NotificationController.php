<?php

namespace App\Controller;

use App\Service\OperatorNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class NotificationController extends AbstractController
{
    public function __construct(
        private readonly OperatorNotificationService $operatorNotificationService
    ) {
    }

    #[Route('/operator-notifications', name: 'admin_operator_notifications')]
    public function operatorNotifications(): JsonResponse
    {
        return new JsonResponse([
            'notifications' => $this->operatorNotificationService->all(),
        ]);
    }

    #[Route('/operator-notifications/remove', name: 'admin_operator_notification_remove', methods: ['POST'])]
    public function removeOperatorNotification(Request $request, OperatorNotificationService $notify): JsonResponse
    {
        $ts = $request->request->getInt('ts');
        if ($ts) {
            $notify->removeByTs($ts);
        }

        return new JsonResponse(['success' => true]);
    }
}
