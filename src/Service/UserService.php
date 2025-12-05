<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Interface\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function getOneByField(string $field, string $value): ?User
    {
        return $this->userRepository->findOneBy([$field => $value]);
    }

    public function getUserByUsername(?object $userInterface): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['id' => $userInterface?->getId()]);
    }
}
