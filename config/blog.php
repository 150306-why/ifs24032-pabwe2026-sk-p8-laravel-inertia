<?php

return [
    'categories' => ['Teknologi', 'Bisnis', 'Gaya Hidup', 'Pendidikan', 'Kesehatan', 'Travel', 'Opini'],
    'force_https' => (bool) env('FORCE_HTTPS', false),
    'ai_max_articles' => (int) env('AI_MAX_ARTICLES', 5),
];
