<?php

namespace App\Service\Product;

use App\Repository\ProductRepository;
use App\Service\Interface\ProductServiceInterface;
use App\Service\Product\Bitrix\ProductAnswerGenerator;
use App\Service\Product\Bitrix\ProductUrlGenerator;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductAnswerGenerator $answerGenerator,
        private readonly ProductUrlGenerator $urlGenerator,
        private readonly ProductRepository $productRepository,
        private string $category,
        private string $limit
    ) {
    }

    public function generateProductAnswer(string $question, array $products, string $title): string
    {
        return $this->answerGenerator->generateAnswer($question, $products, $title);
    }

    public function getProductUrl(array $product): string
    {
        return $this->urlGenerator->generateProductUrl($product);
    }

    public function getNewRandomProducts(): array
    {
        return $this->productRepository->findNewRandomProducts($this->limit, $this->category);
    }

    public function getProductsByQuery(string $userMessage): array
    {
        return $this->productRepository->findProductsByQuery($userMessage, $this->limit);
    }
}
