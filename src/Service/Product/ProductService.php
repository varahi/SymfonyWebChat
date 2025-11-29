<?php

namespace App\Service\Product;

use App\Repository\ProductRepository;

class ProductService
{
    public function __construct(
        private readonly ProductAnswerGenerator $answerGenerator,
        private readonly ProductUrlGenerator $urlGenerator,
        private readonly ProductRepository $productRepository,
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
        return $this->productRepository->findNewRandomProducts($_ENV['PRODUCT_RESULT_LIMIT'], $_ENV['NEW_PRODUCT_CATEGORY']);
    }

    public function getProductsByQuery(string $userMessage)
    {
        return $this->productRepository->findProductsByQuery($userMessage, $_ENV['PRODUCT_RESULT_LIMIT']);
    }
}
