<?php

return [
    'allowed_topics' => $_ENV['ALLOWED_TOPICS'] ?? '*',
    'forbidden_words' => $_ENV['FORBIDDEN_WORDS'] ?? 'политика,религия,насилие,18+,наркотики,экстремизм',
];
