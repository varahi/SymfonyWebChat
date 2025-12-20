<?php

namespace App\Service\Product\Bitrix;

use App\Database\PDOConnection;
use Psr\Log\LoggerInterface;

class ProductUrlGenerator
{
    private \PDO $pdo;

    public function __construct(
        private LoggerInterface $logger,
        private readonly string $baseUrl,
    ) {
        $this->pdo = PDOConnection::getInstance();
    }

    public function generateProductUrl(array $product): string
    {
        $this->logger->info('Generate product URL: start', [
            'product_id' => $product['ID'] ?? null,
            'section_id' => $product['IBLOCK_SECTION_ID'] ?? null,
            'code' => $product['CODE'] ?? null,
        ]);

        $productId = $product['ID'] ?? 0;
        $sectionId = $product['IBLOCK_SECTION_ID'] ?? 0;

        if (!$productId || !$sectionId) {
            $this->logger->warning('Generate product URL: fallback (no productId or sectionId)', [
                'product' => $product,
            ]);

            return $this->baseUrl.'/catalog/';
        }

        // Получаем полный путь категорий
        $categoryPath = $this->getCategoryPath($sectionId);

        $this->logger->info('Category path resolved', [
            'section_id' => $sectionId,
            'category_path' => $categoryPath,
        ]);

        $productCode = $product['CODE'] ?? '';

        if (empty($categoryPath) || empty($productCode)) {
            $this->logger->warning('Generate product URL: fallback (empty path or code)', [
                'category_path' => $categoryPath,
                'product_code' => $productCode,
                'product' => $product,
            ]);

            return $this->baseUrl.'/catalog/';
        }

        $url = $this->baseUrl.'/catalog/'.$categoryPath.'/'.$productCode;
        $this->logger->info('Generate product URL: success', [
            'url' => $url,
        ]);

        return $url;

        // return $this->baseUrl.'/catalog/'.$categoryPath.'/'.$productCode;
    }

    private function getCategoryPathWithRecursion(int $sectionId): string
    {
        try {
            $stmt = $this->pdo->prepare("
                WITH RECURSIVE category_path AS (
                    -- Базовый случай: начальная категория
                    SELECT
                        s.ID,
                        s.CODE,
                        s.IBLOCK_SECTION_ID as PARENT_ID,
                        s.CODE as PATH,
                        1 as LEVEL
                    FROM b_iblock_section s
                    WHERE s.ID = :section_id

                    UNION ALL

                    -- Рекурсивный случай: идем к родителям
                    SELECT
                        s.ID,
                        s.CODE,
                        s.IBLOCK_SECTION_ID,
                        CONCAT(s.CODE, '/', cp.PATH),
                        cp.LEVEL + 1
                    FROM b_iblock_section s
                    INNER JOIN category_path cp ON s.ID = cp.PARENT_ID
                    WHERE s.IBLOCK_SECTION_ID IS NOT NULL
                )
                SELECT PATH FROM category_path
                ORDER BY LEVEL DESC
                LIMIT 1
            ");

            $stmt->execute(['section_id' => $sectionId]);
            $result = $stmt->fetch();

            return $result['PATH'] ?? '';
        } catch (\PDOException $e) {
            error_log('Category path error: '.$e->getMessage());

            return '';
        }
    }

    private function getCategoryPath(int $sectionId): string
    {
        $path = [];

        while ($sectionId) {
            $stmt = $this->pdo->prepare('
            SELECT ID, CODE, IBLOCK_SECTION_ID
            FROM b_iblock_section
            WHERE ID = :id
        ');
            $stmt->execute(['id' => $sectionId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                break;
            }

            array_unshift($path, $row['CODE']);
            $sectionId = $row['IBLOCK_SECTION_ID'];
        }

        return implode('/', $path);
    }
}
