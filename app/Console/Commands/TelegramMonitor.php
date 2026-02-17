<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TelegramMonitor extends Command
{
    protected $signature = 'telegram:monitor {--tail=50 : Número de linhas para mostrar}';
    protected $description = 'Monitora os logs do webhook do Telegram';

    public function handle(): int
    {
        $logFile = storage_path('logs/telegram.log');
        
        if (!File::exists($logFile)) {
            $this->warn('Arquivo de log não encontrado. Envie uma mensagem pelo Telegram primeiro.');
            $this->info('O arquivo será criado em: ' . $logFile);
            return Command::SUCCESS;
        }
        
        $lines = $this->option('tail');
        $content = File::get($logFile);
        $allLines = explode("\n", $content);
        $lastLines = array_slice($allLines, -$lines);
        
        $this->info("=== Últimas {$lines} linhas do log do Telegram ===");
        $this->line('');
        
        foreach ($lastLines as $line) {
            if (empty(trim($line))) continue;
            
            // Colorir logs por nível
            if (str_contains($line, '.ERROR:')) {
                $this->error($line);
            } elseif (str_contains($line, '.WARNING:')) {
                $this->warn($line);
            } elseif (str_contains($line, '.INFO:')) {
                $this->info($line);
            } else {
                $this->line($line);
            }
        }
        
        $this->line('');
        $this->info('Para monitorar em tempo real, use:');
        $this->comment('  tail -f ' . $logFile);
        
        return Command::SUCCESS;
    }
}
