<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $updateId;
    public $chatId;
    public $text;
    public $messageData;

    /**
     * Create a new job instance.
     */
    public function __construct(int $updateId, int $chatId, string $text, array $messageData)
    {
        $this->updateId = $updateId;
        $this->chatId = $chatId;
        $this->text = $text;
        $this->messageData = $messageData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::where('telegram_chat_id', $this->chatId)
                    ->where('telegram_enabled', true)
                    ->first();

        if (!$user) {
            return;
        }

        $telegramService = app(TelegramService::class);

        try {
            if (str_starts_with($this->text, '/')) {
                return;
            }

            $parsed = $telegramService->parseMessage($this->text);
            $category = $telegramService->detectCategory($parsed['descricao'], $user->id, $parsed['tipo']);

            $transaction = $telegramService->createTransaction([
                'valor' => $parsed['valor'],
                'descricao' => $parsed['descricao'],
                'tipo' => $parsed['tipo'],
                'categoria' => $category,
                'card_id' => $user->telegram_default_card_id,
            ], $user);

            $telegramService->sendConfirmation($this->chatId, $transaction);

        } catch (\Exception $e) {
            $this->sendErrorMessage($this->chatId, $e->getMessage());
        }
    }

    /**
     * Send error message to Telegram
     */
    private function sendErrorMessage(int $chatId, string $error): void
    {
        try {
            $token = config('telegram.bot_token');
            if (!$token) return;

            $message = "❌ Erro ao processar mensagem:\n{$error}\n\n";
            $message .= "Formato esperado:\n";
            $message .= "• 150 almoço no restaurante\n";
            $message .= "• recebi 5000 salário";

            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
            ]);
        } catch (\Exception $e) {
            // Silenciosamente ignora erro ao enviar mensagem de erro
        }
    }
}
