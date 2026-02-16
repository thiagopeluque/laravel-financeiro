<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';
    protected $description = 'Configura o webhook do Telegram';

    public function handle(): int
    {
        $token = config('telegram.bot_token');
        $url = config('telegram.webhook_url');

        if (!$token) {
            $this->error('TELEGRAM_BOT_TOKEN não configurado no .env');
            return Command::FAILURE;
        }

        if (!$url) {
            $this->error('TELEGRAM_WEBHOOK_URL não configurado no .env');
            return Command::FAILURE;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $url,
        ]);

        if ($response->successful() && $response->json('ok')) {
            $this->info('Webhook configurado com sucesso!');
            $this->info("URL: {$url}");
            return Command::SUCCESS;
        }

        $this->error('Erro ao configurar webhook: ' . $response->json('description', 'Erro desconhecido'));
        return Command::FAILURE;
    }
}
