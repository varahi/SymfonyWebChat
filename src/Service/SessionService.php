<?php

namespace App\Service;

use App\Entity\ClientSession;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionService
{
    private const SESSION_USER_ID_KEY = 'user_id';

    public function __construct(
        private RequestStack $requestStack,
        private readonly ManagerRegistry $doctrine,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getUserId(): string
    {
        $session = $this->requestStack->getSession();

        if (!$session->has(self::SESSION_USER_ID_KEY)) {
            $userId = 'user_'.bin2hex(random_bytes(8));
            $session->set(self::SESSION_USER_ID_KEY, $userId);
        }

        return $session->get(self::SESSION_USER_ID_KEY);
    }

    public function clearSession(string $sessionId): void
    {
        $em = $this->doctrine->getManager();
        $repo = $em->getRepository(ClientSession::class);
        $session = $repo->findOneBy(['externalId' => $sessionId]);
        if (!$session) {
            $this->logger->warning('Попытка очистить несуществующую сессию', [
                'externalId' => $sessionId,
            ]);

            return;
        }

        $this->logger->info('Удаление сессии', [
            'sessionId' => $session->getId(),
            'externalId' => $sessionId,
        ]);

        $em->remove($session);
        $em->flush();

        $this->logger->info('Сессия успешно удалена', [
            'externalId' => $sessionId,
        ]);
    }

    public function closeSession(string $sessionId): void
    {
        $em = $this->doctrine->getManager();
        $session = $em->getRepository(ClientSession::class)
            ->findOneBy(['externalId' => $sessionId]);

        if (!$session) {
            return;
        }
        $session->setClosedAt(new \DateTimeImmutable());
        $em->flush();
    }
}
