<?php

use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

// Webhook principal do Telegram
Route::post('/telegram/webhook', [TelegramController::class, 'handleTelegramMessage']);

// Endpoint de debug para testar o webhook
Route::get('/telegram/debug', function () {
    $logFile = storage_path('logs/telegram.log');
    
    if (!file_exists($logFile)) {
        return response()->json([
            'status' => 'no_logs',
            'message' => 'Nenhum log encontrado. Envie uma mensagem pelo Telegram primeiro.',
            'log_file' => $logFile
        ]);
    }
    
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -20);
    
    return response()->json([
        'status' => 'ok',
        'last_entries' => $lastLines,
        'log_file' => $logFile,
        'total_lines' => count($lines)
    ]);
});

// Endpoint de teste para simular webhook do Telegram
Route::post('/telegram/test', function () {
    $samplePayload = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 123456789,
                'is_bot' => false,
                'first_name' => 'Test',
                'username' => 'testuser'
            ],
            'chat' => [
                'id' => 123456789,
                'first_name' => 'Test',
                'username' => 'testuser',
                'type' => 'private'
            ],
            'date' => time(),
            'text' => '150 almoço no restaurante'
        ]
    ];
    
    Log::channel('telegram')->info('Teste manual do webhook', [
        'sample_payload' => $samplePayload,
        'note' => 'Use este payload para testar no Postman'
    ]);
    
    return response()->json([
        'status' => 'test_logged',
        'sample_payload' => $samplePayload,
        'instructions' => [
            '1' => 'Configure seu chat_id no perfil',
            '2' => 'Use o payload acima no Postman para testar',
            '3' => 'Acesse /api/telegram/debug para ver os logs'
        ]
    ]);
});
