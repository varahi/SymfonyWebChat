<?php

namespace App\Service\Product;

class ProductAnswerGenerator
{
    public function __construct(
        private ProductUrlGenerator $urlGenerator,
        private ProductImageService $imageService,
    ) {
    }

    public function generateAnswer(string $question, array $products, string $title): string
    {
        $html = "<div class='products-grid'>";
        $html .= "<h4 style='font-size: 14px;'>🎯 $title: ".'</h4>';

        foreach ($products as $product) {
            $html .= "<div class='product-grid-item product-card'>";
            $html .= $this->generateGeneralAnswer($product);
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    private function generateGeneralAnswer(array $product): string
    {
        $detailTrimmedHtml = '';
        $detailHtml = $product['DETAIL_TEXT'] ?? '';
        if ($detailHtml) {
            $detailText = $this->htmlToTextDom($detailHtml);
            $detailTrimmed = mb_strlen($detailText) > 400 ? mb_substr($detailText, 0, 400).'…' : $detailText;
            $detailTrimmedHtml = '<p> 📖 '.$this->truncateText($detailTrimmed, 400).'</p>';
        }

        $url = $this->urlGenerator->generateProductUrl($product);
        $formatedLink = $this->formatLink($url);
        $link = $this->formatMarkdownLink($url, 'перейти на страницу товара');
        $imageUrl = $this->imageService->getProductImageUrl($product, 'small');

        // "🖼️ <div class='product-image'><img src=\"{$imageUrl}\" style=\"max-width: 200px; float: right; margin-left: 10px;\"></div>\n\n".
        $imageHtml = '';
        if ($imageUrl) {
            $imageHtml = "<div class='product-image'><a href=\"{$formatedLink}\" target='_blank'><img src=\"{$imageUrl}\" style=\"max-width: 200px; float: right; margin-left: 10px;\"></a></div>\n\n";
        }

        return "
            <div class='product-info'>
            <h4>📦 {$product['NAME']}</h4>\n\n".
            "{$detailTrimmedHtml}\n".
            "{$imageHtml}\n".
            "🔗 Подробнее: {$link}\n".
            "📞 Консультация: позвоните нам +7 (914) 70-170-09\n".
            '</div>'
        ;
    }

    private function truncateText(string $text, int $length): string
    {
        // Убираем лишние переносы и пробелы
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        // Обрезаем до последнего полного слова
        $truncated = mb_substr($text, 0, $length);
        $lastSpace = mb_strrpos($truncated, ' ');

        if (false !== $lastSpace) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return $truncated.'…';
    }

    private function formatMarkdownLink(string $url, string $text): string
    {
        $encodedUrl = str_replace('_', '%5F', $url);

        return "[{$text}]({$encodedUrl})";
    }

    private function formatLink(string $url): string
    {
        $encodedUrl = str_replace('_', '%5F', $url);

        return "{$encodedUrl}";
    }

    public function htmlToTextDom(string $html): string
    {
        // Подавляем предупреждения парсера
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        // Добавляем мета-charset, чтобы корректно читать UTF-8
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);

        // Заменяем <br> на '\n'
        $brs = $doc->getElementsByTagName('br');
        for ($i = $brs->length - 1; $i >= 0; --$i) {
            $br = $brs->item($i);
            $br->parentNode->replaceChild($doc->createTextNode("\n"), $br);
        }

        // Вставляем двойной перенос после каждого закрытого <p>
        $ps = $doc->getElementsByTagName('p');
        for ($i = $ps->length - 1; $i >= 0; --$i) {
            $p = $ps->item($i);
            if ($p->nextSibling) {
                $p->parentNode->insertBefore($doc->createTextNode("\n\n"), $p->nextSibling);
            } else {
                $p->parentNode->appendChild($doc->createTextNode("\n\n"));
            }
        }

        $text = $doc->textContent ?? '';
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xC2\xA0", ' ', $text);
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);

        return trim($text);
    }
}
