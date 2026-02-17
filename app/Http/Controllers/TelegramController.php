<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTelegramMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

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

        // Validar payload
        if (!$updateId || !$message) {
            return response()->json(['status' => 'ignored'], 200);
        }

        // Verificar duplicata (Telegram pode reenviar)
        $cacheKey = "telegram_update_{$updateId}";
        if (Cache::has($cacheKey)) {
            return response()->json(['status' => 'already_processed'], 200);
        }

        // Marcar como processado (evita duplicatas por 1 hora)
        Cache::put($cacheKey, true, now()->addHour());

        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? null;

        if (!$chatId || !$text) {
            return response()->json(['status' => 'invalid_data'], 200);
        }

        // Ignorar comandos do bot
        if (str_starts_with($text, '/')) {
            return response()->json(['status' => 'command_ignored'], 200);
        }

        // Despachar job para processamento assíncrono
        ProcessTelegramMessage::dispatch($updateId, $chatId, $text, $message);

        // Responder imediatamente com sucesso
        return response()->json([
            'status' => 'accepted',
            'update_id' => $updateId
        ], 200);
    }
}
