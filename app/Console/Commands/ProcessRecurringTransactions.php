<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'transactions:process-recurring {--month= : Month to process (format: Y-m)}';

    protected $description = 'Process recurring transactions and create monthly instances';

    public function handle(): int
    {
        $monthParam = $this->option('month');

        if ($monthParam) {
            $targetDate = new \DateTime($monthParam.'-01');
        } else {
            $targetDate = new \DateTime('first day of next month');
        }

        $targetMonth = (int) $targetDate->format('n');
        $targetYear = (int) $targetDate->format('Y');

        $recurringTransactions = Transaction::where('recorrente', true)
            ->where('recorrente_ativa', true)
            ->whereDate('recorrente_ate', '>=', $targetDate->format('Y-m-d'))
            ->get();

        $created = 0;

        foreach ($recurringTransactions as $parentTransaction) {
            $existingChild = Transaction::where('transacao_recorrente_pai_id', $parentTransaction->id)
                ->whereMonth('data', $targetMonth)
                ->whereYear('data', $targetYear)
                ->exists();

            if ($existingChild) {
                continue;
            }

            $card = $parentTransaction->card;
            $dataTransacao = $this->calculateRecurringDate(
                $parentTransaction->data,
                $targetMonth,
                $targetYear,
                $card
            );

            Transaction::create([
                'user_id' => $parentTransaction->user_id,
                'category_id' => $parentTransaction->category_id,
                'card_id' => $parentTransaction->card_id,
                'valor' => $parentTransaction->valor,
                'descricao' => $parentTransaction->descricao,
                'data' => $dataTransacao,
                'observacoes' => $parentTransaction->observacoes,
                'total_parcelas' => 1,
                'parcela_atual' => 1,
                'transacao_pai_id' => null,
                'recorrente' => false,
                'recorrente_ate' => null,
                'transacao_recorrente_pai_id' => $parentTransaction->id,
                'recorrente_ativa' => true,
            ]);

            $created++;
            Log::info("Created recurring transaction for user {$parentTransaction->user_id}, month {$targetYear}-{$targetMonth}");
        }

        $this->info("Created {$created} recurring transactions for {$targetYear}-".str_pad($targetMonth, 2, '0', STR_PAD_LEFT));

        return Command::SUCCESS;
    }

    private function calculateRecurringDate(string $initialDate, int $month, int $year, ?\App\Models\Card $card): string
    {
        $initial = new \DateTime($initialDate);
        $date = new \DateTime;
        $date->setDate($year, $month, (int) $initial->format('j'));

        if ($card && $card->vencimento_fatura) {
            $date->setDate($year, $month, $card->vencimento_fatura);
        }

        return $date->format('Y-m-d');
    }
}
