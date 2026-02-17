<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\TelegramService;

class TelegramController extends Controller
{
    public function handleTelegramMessage(Request $request): JsonResponse
    {
        $data = $request->all();
        
        // Log para debug - salva o payload completo do Telegram
        \Log::channel('telegram')->info('Webhook recebido', [
            'payload' => $data,
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        $message = $data['message'] ?? null;

        if (!$message) {
            \Log::channel('telegram')->warning('Mensagem não encontrada no payload', ['data' => $data]);
            return response()->json(['status' => 'error', 'message' => 'No message in payload'], 400);
        }

        $chatId = $message['chat']['id'];
        $text = $message['text'];
        
        \Log::channel('telegram')->info('Processando mensagem', [
            'chat_id' => $chatId,
            'text' => $text,
            'from' => $message['from'] ?? null
        ]);
        
        // Ignorar comandos do bot (começam com /)
        if (str_starts_with($text, '/')) {
            \Log::channel('telegram')->info('Comando do bot ignorado', ['command' => $text]);
            return response()->json(['status' => 'ignored', 'message' => 'Bot commands are ignored']);
        }

        $user = User::where('telegram_chat_id', $chatId)
                    ->where('telegram_enabled', true)
                    ->first();

        if (!$user) {
            \Log::channel('telegram')->warning('Usuário não encontrado ou desativado', [
                'chat_id' => $chatId,
                'text' => $text
            ]);
            return response()->json(['status' => 'user not found'], 404);
        }
        
        \Log::channel('telegram')->info('Usuário encontrado', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'chat_id' => $chatId
        ]);

        $telegramService = app(TelegramService::class);

        try {
            \Log::channel('telegram')->info('Iniciando parse da mensagem', ['text' => $text]);
            $parsed = $telegramService->parseMessage($text);
            
            \Log::channel('telegram')->info('Mensagem parseada', [
                'valor' => $parsed['valor'],
                'descricao' => $parsed['descricao'],
                'tipo' => $parsed['tipo']
            ]);
            
            $category = $telegramService->detectCategory($parsed['descricao'], $user->id, $parsed['tipo']);
            
            \Log::channel('telegram')->info('Categoria detectada/criada', [
                'category_id' => $category->id,
                'category_name' => $category->nome
            ]);

            $transaction = $telegramService->createTransaction([
                'valor' => $parsed['valor'],
                'descricao' => $parsed['descricao'],
                'tipo' => $parsed['tipo'],
                'categoria' => $category,
                'card_id' => $user->telegram_default_card_id,
            ], $user);
            
            \Log::channel('telegram')->info('Transação criada com sucesso', [
                'transaction_id' => $transaction->id,
                'valor' => $transaction->valor
            ]);

            $telegramService->sendConfirmation($chatId, $transaction);
            
            \Log::channel('telegram')->info('Confirmação enviada ao Telegram');

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            \Log::channel('telegram')->error('Erro ao processar mensagem', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
