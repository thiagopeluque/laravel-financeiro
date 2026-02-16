<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    'enabled' => env('TELEGRAM_ENABLED', false),
    'category_keywords' => [
        'academia' => ['academia', 'musculação', 'fitness', 'ginásio'],
        'supermercado' => ['mercado', 'supermercado', 'compras'],
        'combustível' => ['gasolina', 'posto', 'combustível', 'etanol'],
        'transporte_app' => ['uber', '99', 'taxi', 'transporte'],
        'streaming' => ['netflix', 'spotify', 'streaming', 'disney'],
        'saúde' => ['médico', 'consulta', 'remédio', 'farmácia', 'medicamento'],
        'alimentação' => ['lanche', 'ifood', 'delivery', 'pizza', 'hambúrguer'],
        'salário' => ['salário', 'pagamento', 'remuneração'],
        'freelance' => ['freelance', 'projeto', 'extra', 'freela'],
    ],
];
