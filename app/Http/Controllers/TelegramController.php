<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTelegramMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * Handle incoming Telegram webhook
     * Responde imediatamente com 200 OK e processa em background
     */
    public function handleTelegramMessage(Request $request): JsonResponse
    {
        $data = $request->all();
        $updateId = $data['update_id'] ?? null;
        $message = $data['message'] ?? null;

        // Log básico para debug
        Log::channel('telegram')->info('Webhook recebido', [
            'update_id' => $updateId,
            'has_message' => !is_null($message),
            'timestamp' => now()->toDateTimeString()
        ]);

        // Validar payload
        if (!$updateId || !$message) {
            Log::channel('telegram')->warning('Payload inválido', ['data' => array_keys($data)]);
            return response()->json(['status' => 'ignored'], 200);
        }

        // Verificar duplicata (Telegram pode reenviar)
        $cacheKey = "telegram_update_{$updateId}";
        if (Cache::has($cacheKey)) {
            Log::channel('telegram')->info('Update duplicado ignorado', ['update_id' => $updateId]);
            return response()->json(['status' => 'already_processed'], 200);
        }

        // Marcar como processado (evita duplicatas por 1 hora)
        Cache::put($cacheKey, true, now()->addHour());

        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? null;

        if (!$chatId || !$text) {
            Log::channel('telegram')->warning('Dados da mensagem incompletos', [
                'chat_id' => $chatId,
                'has_text' => !is_null($text)
            ]);
            return response()->json(['status' => 'invalid_data'], 200);
        }

        // Ignorar comandos do bot
        if (str_starts_with($text, '/')) {
            Log::channel('telegram')->info('Comando ignorado', [
                'command' => $text,
                'update_id' => $updateId
            ]);
            return response()->json(['status' => 'command_ignored'], 200);
        }

        // Log da mensagem recebida
        Log::channel('telegram')->info('Mensagem válida recebida', [
            'update_id' => $updateId,
            'chat_id' => $chatId,
            'text' => $text
        ]);

        // Despachar job para processamento assíncrono
        // Isso garante que respondemos 200 OK imediatamente ao Telegram
        ProcessTelegramMessage::dispatch($updateId, $chatId, $text, $message);

        Log::channel('telegram')->info('Job despachado', ['update_id' => $updateId]);

        // Responder imediatamente com sucesso
        // Isso evita que o Telegram reenvie a mensagem
        return response()->json([
            'status' => 'accepted',
            'update_id' => $updateId,
            'message' => 'Processando em background'
        ], 200);
    }
}
