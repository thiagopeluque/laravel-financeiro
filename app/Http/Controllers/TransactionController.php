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
            ];

            if ($i === 0 && $parcelas > 1) {
                $parentTransaction = Auth::user()->transactions()->create($transactionData);
                $transactionData['transacao_pai_id'] = $parentTransaction->id;
                $parentTransaction->update(['transacao_pai_id' => $parentTransaction->id]);
            } elseif ($parcelas > 1) {
                $transactionData['transacao_pai_id'] = $parentTransaction->id;
                Auth::user()->transactions()->create($transactionData);
            } else {
                Auth::user()->transactions()->create($transactionData);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Transação criada com sucesso.');
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

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'card_id' => 'nullable|exists:cards,id',
            'valor' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:255',
            'data' => 'required|date',
            'observacoes' => 'nullable|string',
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

        $transaction->update($validated);

        return redirect()->route('dashboard')->with('success', 'Transação atualizada com sucesso.');
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
