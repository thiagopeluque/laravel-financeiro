<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Services\TelegramService;

class TelegramController extends Controller
{
    public function handleTelegramMessage(Request $request): JsonResponse
    {
        $data = $request->all();
        $message = $data['message'] ?? null;

        if (!$message) {
            return response()->json(['status' => 'error'], 400);
        }

        $chatId = $message['chat']['id'];
        $text = $message['text'];

        $user = User::where('telegram_chat_id', $chatId)
                    ->where('telegram_enabled', true)
                    ->first();

        if (!$user) {
            return response()->json(['status' => 'user not found'], 404);
        }

        $telegramService = app(TelegramService::class);

        try {
            $parsed = $telegramService->parseMessage($text);
            $category = $telegramService->detectCategory($parsed['descricao'], $user->id, $parsed['tipo']);

            $transaction = $telegramService->createTransaction([
                'valor' => $parsed['valor'],
                'descricao' => $parsed['descricao'],
                'tipo' => $parsed['tipo'],
                'categoria' => $category,
                'card_id' => $user->telegram_default_card_id,
            ], $user);

            $telegramService->sendConfirmation($chatId, $transaction);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
