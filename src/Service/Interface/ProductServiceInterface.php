<?php

namespace App\Service\Interface;

interface ProductServiceInterface
{
    public function generateProductAnswer(string $question, array $products, string $title): string;

    public function getProductUrl(array $product): string;

    public function getNewRandomProducts(): array;

    public function getProductsByQuery(string $userMessage): array;
}
