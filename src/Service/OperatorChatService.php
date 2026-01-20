<?php

namespace App\Service;

use App\Entity\ClientSession;
use App\Entity\Message;
use App\Enum\ClientSessionStatus;
use App\Enum\MessageRole;
use App\Enum\MessageStatus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OperatorChatService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly SessionService $sessionService,
        private LoggerInterface $chatLogger,
    ) {
    }

    /**
     * Получаем или создаём клиентскую сессию (по userId из фронтенда).
     */
    public function getOrCreateClientSession(): ClientSession
    {
        $userId = $this->sessionService->getUserId();

        $session = $this->em->getRepository(ClientSession::class)
            ->findOneBy([
                'externalId' => $userId,
                'closedAt' => null,
            ]);

        //        $this->chatLogger->info('Найдено совпадение session', [
        //            'externalId' => $userId,
        //            'closedAt' => null,
        //            'status' => ClientSessionStatus::OPENED
        //        ]);

        if (!$session) {
            $session = new ClientSession();
            $session->setExternalId($userId);
            $session->setStatus(ClientSessionStatus::CLOSED);
            $session->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($session);
            $this->em->flush();
        }

        return $session;
    }

    public function getClientSession(): ClientSession
    {
        $userId = $this->sessionService->getUserId();

        $session = $this->em->getRepository(ClientSession::class)
            ->findOneBy([
                'externalId' => $userId,
                'closedAt' => null,
                // 'status' => ClientSessionStatus::OPENED,
            ]);

        return $session;
    }

    /**
     * Записываем сообщение клиента.
     */
    public function storeClientMessage(ClientSession $session, string $text): void
    {
        $msg = new Message();
        $msg->setClientSession($session);
        $msg->setRole(MessageRole::CLIENT);
        $msg->setStatus(MessageStatus::CREATED);
        $msg->setMessage($text);
        $msg->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($msg);
        $this->em->flush();
    }

    /**
     * Записываем сообщение оператора (когда админ отвечает в EasyAdmin).
     */
    //    public function storeOperatorMessage(ClientSession $session, string $text): void
    //    {
    //        $operator = $this->security->getUser();
    //
    //        $msg = new Message();
    //        $msg->setClientSession($session);
    //        $msg->setRole(MessageRole::OPERATOR);
    //        $msg->setStatus(MessageStatus::PROCESSED);
    //        $msg->setOperator($operator);
    //        $msg->setMessage($text);
    //        $msg->setCreatedAt(new \DateTimeImmutable());
    //
    //        $this->em->persist($msg);
    //        $this->em->flush();
    //    }

    /**
     * Получить последние N сообщений — для контекста LLM.
     */
    public function getHistory(ClientSession $session, int $limit = 5): array
    {
        $messages = $session->getMessages()->toArray();
        $messages = array_slice($messages, -$limit);

        return array_map(fn (Message $m) => [
            'role' => strtolower($m->getRole()->value),
            'content' => $m->getText(),
        ], $messages);
    }

    /*
     * Закрыть клиентскую сессию.
     */
    //    public function closeSession(ClientSession $session): void
    //    {
    //        $session->setStatus(ClientSessionStatus::CLOSED);
    //        $session->setClosedAt(new \DateTimeImmutable());
    //        $this->em->flush();
    //    }

    public function pushOperatorMessage(string $sessionId, string $text): void
    {
        if (!isset($_SESSION['operator_messages'][$sessionId])) {
            $_SESSION['operator_messages'][$sessionId] = [];
        }

        $_SESSION['operator_messages'][$sessionId][] = [
            'text' => $text,
            'ts' => time(),
            'type' => 'system',
        ];
    }

    public function getOperatorMessages(string $sessionId): array
    {
        return $_SESSION['operator_messages'][$sessionId] ?? [];
    }
}
