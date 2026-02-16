<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->transactions()->with('category');

        if ($request->filled('mes')) {
            $query->whereMonth('data', $request->mes)
                ->whereYear('data', $request->ano ?? date('Y'));
        }

        if ($request->filled('tipo')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('tipo', $request->tipo);
            });
        }

        $transactions = $query->orderBy('data', 'desc')->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $categories = Auth::user()->categories()->orderBy('nome')->get();

        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'card_id' => 'nullable|exists:cards,id',
            'valor' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:255',
            'data' => 'required|date',
            'observacoes' => 'nullable|string',
            'parcelas' => 'nullable|integer|min:1|max:120',
            'recorrente' => 'nullable|boolean',
            'recorrente_ate' => 'nullable|date|after_or_equal:data',
        ]);

        $category = Category::find($request->category_id);
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $card = null;
        if ($request->filled('card_id')) {
            $card = Card::find($request->card_id);
            if ($card->user_id !== Auth::id()) {
                abort(403);
            }
        }

        $isRecurring = $request->boolean('recorrente');
        $recorrenteAte = $request->recorrente_ate;

        $parcelas = $request->parcelas ?? 1;
        $valorParcela = $request->valor / $parcelas;

        $parentTransaction = null;

        for ($i = 0; $i < $parcelas; $i++) {
            $transactionData = [
                'user_id' => Auth::id(),
                'category_id' => $request->category_id,
                'card_id' => $request->card_id,
                'valor' => $valorParcela,
                'descricao' => $request->descricao,
                'data' => $this->calculateInstallmentDate($request->data, $i, $card),
                'observacoes' => $request->observacoes,
                'total_parcelas' => $parcelas,
                'parcela_atual' => $i + 1,
                'transacao_pai_id' => null,
                'recorrente' => $isRecurring,
                'recorrente_ate' => $isRecurring ? $recorrenteAte : null,
                'recorrente_ativa' => $isRecurring,
            ];

            if ($i === 0 && $parcelas > 1) {
                $parentTransaction = Auth::user()->transactions()->create($transactionData);
                $transactionData['transacao_pai_id'] = $parentTransaction->id;
                $parentTransaction->update(['transacao_pai_id' => $parentTransaction->id]);
            } elseif ($parcelas > 1) {
                $transactionData['transacao_pai_id'] = $parentTransaction->id;
                Auth::user()->transactions()->create($transactionData);
            } else {
                $parentTransaction = Auth::user()->transactions()->create($transactionData);
            }
        }

        if ($isRecurring && $parcelas === 1) {
            $this->createRecurringInstances($parentTransaction, $card, $recorrenteAte);
        }

        return redirect()->route('dashboard')->with('success', 'Transação criada com sucesso.');
    }

    private function createRecurringInstances(Transaction $parentTransaction, ?Card $card, string $recorrenteAte): void
    {
        $currentDate = new \DateTime($parentTransaction->data);
        $endDate = new \DateTime($recorrenteAte);

        while ($currentDate < $endDate) {
            $currentDate->modify('+1 month');

            if ($currentDate > $endDate) {
                break;
            }

            $dataTransacao = $this->calculateRecurringDate(
                $parentTransaction->data,
                (int) $currentDate->format('n'),
                (int) $currentDate->format('Y'),
                $card
            );

            Auth::user()->transactions()->create([
                'user_id' => Auth::id(),
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
        }
    }

    private function calculateRecurringDate(string $initialDate, int $month, int $year, ?Card $card): string
    {
        $initial = new \DateTime($initialDate);
        $date = new \DateTime;
        $date->setDate($year, $month, (int) $initial->format('j'));

        if ($card && $card->vencimento_fatura) {
            $date->setDate($year, $month, $card->vencimento_fatura);
        }

        return $date->format('Y-m-d');
    }

    private function calculateInstallmentDate(string $initialDate, int $installmentIndex, ?Card $card): string
    {
        $date = new \DateTime($initialDate);

        if ($card && $card->vencimento_fatura) {
            $currentDay = (int) $date->format('j');
            $closingDay = $card->fechamento_fatura;
            $dueDay = $card->vencimento_fatura;

            if ($currentDay > $closingDay) {
                $date->modify('+1 month');
            }

            $date->setDate((int) $date->format('Y'), (int) $date->format('n'), $dueDay);
            $date->modify("+{$installmentIndex} month");
        } else {
            $date->modify("+{$installmentIndex} month");
        }

        return $date->format('Y-m-d');
    }

    public function edit(Transaction $transaction)
    {
        $this->authorizeUser($transaction);
        $categories = Auth::user()->categories()->orderBy('nome')->get();

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorizeUser($transaction);

        if ($transaction->isRecurringChild()) {
            return redirect()->route('transactions.index')->with('error', 'Transações geradas automaticamente não podem ser editadas.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'card_id' => 'nullable|exists:cards,id',
            'valor' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:255',
            'data' => 'required|date',
            'observacoes' => 'nullable|string',
            'recorrente' => 'nullable|boolean',
            'recorrente_ate' => 'nullable|date|after_or_equal:data',
        ]);

        $category = Category::find($request->category_id);
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        if ($request->filled('card_id')) {
            $card = Card::find($request->card_id);
            if ($card->user_id !== Auth::id()) {
                abort(403);
            }
        }

        $isRecurring = $request->boolean('recorrente');
        $recorrenteAte = $request->recorrente_ate;

        $updateData = [
            'category_id' => $request->category_id,
            'card_id' => $request->card_id,
            'valor' => $request->valor,
            'descricao' => $request->descricao,
            'data' => $request->data,
            'observacoes' => $request->observacoes,
            'recorrente' => $isRecurring,
            'recorrente_ate' => $isRecurring ? $recorrenteAte : null,
            'recorrente_ativa' => $isRecurring,
        ];

        $wasRecurring = $transaction->recorrente;
        $transaction->update($updateData);

        if ($isRecurring && $recorrenteAte) {
            $this->updateRecurringInstances($transaction, $recorrenteAte);
        } elseif ($wasRecurring && ! $isRecurring) {
            $transaction->recurringChildren()->update(['recorrente_ativa' => false]);
        }

        return redirect()->route('transactions.index')->with('success', 'Transação atualizada com sucesso.');
    }

    private function updateRecurringInstances(Transaction $parentTransaction, string $recorrenteAte): void
    {
        $parentTransaction->recurringChildren()->delete();

        $endDate = new \DateTime($recorrenteAte);
        $currentDate = new \DateTime($parentTransaction->data);

        while ($currentDate < $endDate) {
            $currentDate->modify('+1 month');

            if ($currentDate > $endDate) {
                break;
            }

            Auth::user()->transactions()->create([
                'user_id' => Auth::id(),
                'category_id' => $parentTransaction->category_id,
                'card_id' => $parentTransaction->card_id,
                'valor' => $parentTransaction->valor,
                'descricao' => $parentTransaction->descricao,
                'data' => $currentDate->format('Y-m-d'),
                'observacoes' => $parentTransaction->observacoes,
                'total_parcelas' => 1,
                'parcela_atual' => 1,
                'transacao_pai_id' => null,
                'recorrente' => false,
                'recorrente_ate' => null,
                'transacao_recorrente_pai_id' => $parentTransaction->id,
                'recorrente_ativa' => true,
            ]);
        }
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorizeUser($transaction);
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transação excluída com sucesso.');
    }

    private function authorizeUser(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
