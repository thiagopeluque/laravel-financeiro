<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramRemoveWebhook extends Command
{
    protected $signature = 'telegram:remove-webhook';
    protected $description = 'Remove o webhook do Telegram';

    public function handle(): int
    {
        $token = config('telegram.bot_token');

        if (!$token) {
            $this->error('TELEGRAM_BOT_TOKEN não configurado no .env');
            return Command::FAILURE;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/deleteWebhook");

        if ($response->successful() && $response->json('ok')) {
            $this->info('Webhook removido com sucesso!');
            return Command::SUCCESS;
        }

        $this->error('Erro ao remover webhook: ' . $response->json('description', 'Erro desconhecido'));
        return Command::FAILURE;
    }
}
