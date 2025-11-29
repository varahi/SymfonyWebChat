<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class SessionService
{
    private const SESSION_USER_ID_KEY = 'user_id';

    public function __construct(
        private RequestStack $requestStack
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
}
