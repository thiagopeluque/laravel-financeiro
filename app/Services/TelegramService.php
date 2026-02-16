<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function parseMessage(string $text): array
    {
        $text = trim($text);
        $words = explode(' ', $text);
        
        $tipo = 'despesa';
        
        if (strtolower($words[0]) === 'recebi') {
            $tipo = 'receita';
            array_shift($words);
            $text = implode(' ', $words);
        }
        
        preg_match('/(\d+[.,]?\d*(?:[.,]\d{2})?)/', $text, $matches);
        
        if (!isset($matches[1])) {
            throw new \Exception('Valor não encontrado na mensagem');
        }
        
        $valorStr = $matches[1];
        $valor = $this->parseValor($valorStr);
        
        $descricao = trim(str_replace($matches[0], '', $text));
        $descricao = preg_replace('/^\s*no\s+/i', '', $descricao);
        $descricao = preg_replace('/^\s*na\s+/i', '', $descricao);
        $descricao = ucfirst($descricao);
        
        return [
            'valor' => $valor,
            'descricao' => $descricao,
            'tipo' => $tipo,
            'palavras' => explode(' ', strtolower($descricao)),
        ];
    }
    
    private function parseValor(string $valorStr): float
    {
        $valorStr = str_replace(',', '.', $valorStr);
        
        if (substr_count($valorStr, '.') > 1) {
            $valorStr = str_replace('.', '', $valorStr);
            $valorStr = substr_replace($valorStr, '.', -2, 0);
        }
        
        return (float) $valorStr;
    }
    
    public function detectCategory(string $descricao, int $userId, string $tipo): Category
    {
        $palavras = explode(' ', strtolower($descricao));
        $categorias = Category::where('user_id', $userId)
            ->where('tipo', $tipo)
            ->get();
        
        $melhorCategoria = null;
        $melhorSimilaridade = 0;
        
        foreach ($categorias as $categoria) {
            $nomeCategoria = strtolower($categoria->nome);
            $similaridade = $this->calcularSimilaridade($palavras, $nomeCategoria);
            
            if ($similaridade > $melhorSimilaridade) {
                $melhorSimilaridade = $similaridade;
                $melhorCategoria = $categoria;
            }
        }
        
        if ($melhorCategoria && $melhorSimilaridade >= 60) {
            return $melhorCategoria;
        }
        
        $palavrasSignificativas = array_filter($palavras, function($palavra) {
            $palavrasIgnoradas = ['no', 'na', 'do', 'da', 'de', 'em', 'um', 'uma', 'o', 'a', 'os', 'as'];
            return strlen($palavra) > 2 && !in_array($palavra, $palavrasIgnoradas);
        });
        
        $nomeNovaCategoria = implode(' ', array_slice($palavrasSignificativas, 0, 2));
        
        if (empty($nomeNovaCategoria)) {
            $nomeNovaCategoria = ucfirst($descricao);
        } else {
            $nomeNovaCategoria = ucfirst($nomeNovaCategoria);
        }
        
        return Category::create([
            'user_id' => $userId,
            'nome' => $nomeNovaCategoria,
            'tipo' => $tipo,
        ]);
    }
    
    private function calcularSimilaridade(array $palavrasDescricao, string $nomeCategoria): int
    {
        $maxSimilaridade = 0;
        
        foreach ($palavrasDescricao as $palavra) {
            similar_text($palavra, $nomeCategoria, $percent);
            if ($percent > $maxSimilaridade) {
                $maxSimilaridade = $percent;
            }
            
            $distancia = levenshtein($palavra, $nomeCategoria);
            $maxLen = max(strlen($palavra), strlen($nomeCategoria));
            if ($maxLen > 0) {
                $similaridadeLev = (1 - $distancia / $maxLen) * 100;
                if ($similaridadeLev > $maxSimilaridade) {
                    $maxSimilaridade = $similaridadeLev;
                }
            }
        }
        
        $palavrasCategoria = explode(' ', $nomeCategoria);
        foreach ($palavrasCategoria as $palavraCat) {
            foreach ($palavrasDescricao as $palavra) {
                if (strpos($palavra, $palavraCat) !== false || strpos($palavraCat, $palavra) !== false) {
                    $maxSimilaridade = max($maxSimilaridade, 70);
                }
            }
        }
        
        return (int) $maxSimilaridade;
    }
    
    public function createTransaction(array $data, User $user): Transaction
    {
        $transaction = new Transaction([
            'user_id' => $user->id,
            'category_id' => $data['categoria']->id,
            'valor' => $data['valor'],
            'descricao' => $data['descricao'],
            'data' => now(),
            'observacoes' => 'Registrado via Telegram',
        ]);
        
        if ($data['card_id'] ?? $user->telegram_default_card_id) {
            $transaction->card_id = $data['card_id'] ?? $user->telegram_default_card_id;
        }
        
        $transaction->save();
        
        return $transaction;
    }
    
    public function sendConfirmation(string $chatId, Transaction $transaction): void
    {
        $token = config('telegram.bot_token');
        
        if (!$token) {
            throw new \Exception('Token do Telegram não configurado');
        }
        
        $tipo = $transaction->category && $transaction->category->tipo === 'receita' ? 'Receita' : 'Despesa';
        $valor = number_format($transaction->valor, 2, ',', '.');
        $categoria = $transaction->category ? $transaction->category->nome : 'Sem categoria';
        
        $mensagem = "✅ Lançado: {$tipo} de R$ {$valor}\n";
        $mensagem .= "Descrição: {$transaction->descricao}\n";
        $mensagem .= "Categoria: {$categoria}";
        
        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $mensagem,
            'parse_mode' => 'HTML',
        ]);
    }
}
