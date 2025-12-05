<?php

namespace App\Service\Interface;

use App\Entity\User;

interface UserServiceInterface
{
    public function getOneByField(string $field, string $value): ?User;

    public function getUserByUsername(?object $userInterface): ?User;
}
