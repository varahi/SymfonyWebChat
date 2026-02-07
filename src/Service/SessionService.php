<?php

namespace App\Service;

use App\Entity\ClientSession;
use App\Enum\ClientSessionStatus;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Uid\Uuid;

class SessionService
{
    private const SESSION_USER_ID_KEY = 'user_id';

    public function __construct(
        private RequestStack $requestStack,
        private readonly ManagerRegistry $doctrine,
        private readonly LoggerInterface $logger,
        private readonly KernelInterface $kernel,
    ) {
    }

    public function getUserId(): string
    {
        $session = $this->requestStack->getSession();

        if (!$session->has(self::SESSION_USER_ID_KEY)) {
            // $userId = 'user_'.bin2hex(random_bytes(8));
            $userId = 'user_'.Uuid::v4();
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

        $this->removePhpSession();

        $this->logger->info('Сессия успешно удалена', [
            'externalId' => $sessionId,
        ]);
    }

    public function closeSessionByAdmin(string $sessionId): void
    {
        $em = $this->doctrine->getManager();
        $session = $em->getRepository(ClientSession::class)
            ->findOneBy(['id' => $sessionId]);

        if (!$session) {
            return;
        }
        $session->setClosedAt(new \DateTimeImmutable());
        $session->setStatus(ClientSessionStatus::CLOSED);
        $em->flush();

        $this->removePhpSession();
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
        $session->setStatus(ClientSessionStatus::CLOSED);
        $em->flush();

        $this->removePhpSession();
    }

    public function operatorStartedSession(string $sessionId): void
    {
        $em = $this->doctrine->getManager();
        $session = $em->getRepository(ClientSession::class)
            ->findOneBy(['id' => $sessionId]);

        if (!$session) {
            return;
        }

        if ('dev' === $this->kernel->getEnvironment()) {
            $this->logger->warning('Get session by operatorStartedSession', [
                'session' => $session->getId(),
            ]);
        }

        $session->setStatus(ClientSessionStatus::OPERATOR_STARTED);
        $em->flush();

        $this->removePhpSession();
    }

    public function openSession(string $sessionId): void
    {
        $em = $this->doctrine->getManager();
        $session = $em->getRepository(ClientSession::class)
            ->findOneBy(['externalId' => $sessionId]);

        if (!$session) {
            return;
        }
        // $session->setClosedAt(new \DateTimeImmutable());
        $session->setStatus(ClientSessionStatus::OPENED);
        $em->flush();

        // $this->removePhpSession();
    }

    private function removePhpSession(): void
    {
        $phpSession = $this->requestStack->getSession();
        $phpSession->remove(SessionService::SESSION_USER_ID_KEY);
        $phpSession->migrate(true); // создаёт новое PHP session_id
    }

    public function isOperatorSession(ClientSession $session, string $userId): bool
    {
        if ($session->getExternalId() !== $userId) {
            return false;
        }

        return in_array(
            $session->getStatus(),
            [
                ClientSessionStatus::OPERATOR_STARTED,
                ClientSessionStatus::OPENED,
            ],
            true
        );
    }
}
