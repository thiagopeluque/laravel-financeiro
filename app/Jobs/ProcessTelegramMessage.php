<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        Log::channel('telegram')->info('Job iniciado', [
            'update_id' => $this->updateId,
            'chat_id' => $this->chatId,
            'text' => $this->text
        ]);

        // Buscar usuário
        $user = User::where('telegram_chat_id', $this->chatId)
                    ->where('telegram_enabled', true)
                    ->first();

        if (!$user) {
            Log::channel('telegram')->warning('Usuário não encontrado no job', [
                'chat_id' => $this->chatId
            ]);
            return;
        }

        $telegramService = app(TelegramService::class);

        try {
            // Verificar se não é comando do bot
            if (str_starts_with($this->text, '/')) {
                Log::channel('telegram')->info('Comando ignorado no job', ['command' => $this->text]);
                return;
            }

            // Parse da mensagem
            $parsed = $telegramService->parseMessage($this->text);
            
            Log::channel('telegram')->info('Mensagem parseada no job', [
                'valor' => $parsed['valor'],
                'descricao' => $parsed['descricao'],
                'tipo' => $parsed['tipo']
            ]);

            // Detectar/criar categoria
            $category = $telegramService->detectCategory($parsed['descricao'], $user->id, $parsed['tipo']);
            
            Log::channel('telegram')->info('Categoria no job', [
                'category_id' => $category->id,
                'category_name' => $category->nome
            ]);

            // Criar transação
            $transaction = $telegramService->createTransaction([
                'valor' => $parsed['valor'],
                'descricao' => $parsed['descricao'],
                'tipo' => $parsed['tipo'],
                'categoria' => $category,
                'card_id' => $user->telegram_default_card_id,
            ], $user);

            Log::channel('telegram')->info('Transação criada no job', [
                'transaction_id' => $transaction->id,
                'update_id' => $this->updateId
            ]);

            // Enviar confirmação
            $telegramService->sendConfirmation($this->chatId, $transaction);
            
            Log::channel('telegram')->info('Job completado com sucesso', [
                'update_id' => $this->updateId
            ]);

        } catch (\Exception $e) {
            Log::channel('telegram')->error('Erro no job', [
                'update_id' => $this->updateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Enviar mensagem de erro ao usuário
            $this->sendErrorMessage($this->chatId, $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('telegram')->error('Job falhou', [
            'update_id' => $this->updateId,
            'error' => $exception->getMessage()
        ]);
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
            Log::channel('telegram')->error('Erro ao enviar mensagem de erro', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
