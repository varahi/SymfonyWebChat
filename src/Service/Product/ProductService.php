<?php

namespace App\Service\Product;

use App\Repository\ProductRepository;

class ProductService
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

    public function getNewRandomProducts()
    {
        return $this->productRepository->findNewRandomProducts($this->limit, $this->category);
    }

    public function getProductsByQuery(string $userMessage)
    {
        return $this->productRepository->findProductsByQuery($userMessage, $this->limit);
    }
}
