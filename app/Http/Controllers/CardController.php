<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function index()
    {
        $cards = Auth::user()->cards()
            ->with(['transactions' => function ($q) {
                $q->whereMonth('data', date('m'))
                    ->whereYear('data', date('Y'))
                    ->with('category')
                    ->orderBy('data', 'desc');
            }])
            ->orderBy('nome')
            ->paginate(12);

        return view('cards.index', compact('cards'));
    }

    public function create()
    {
        return view('cards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'bandeira' => 'required|string|max:50',
            'ultimos_digitos' => 'required|string|size:4',
            'limite' => 'nullable|numeric|min:0',
            'fechamento_fatura' => 'nullable|integer|min:1|max:31',
            'vencimento_fatura' => 'nullable|integer|min:1|max:31',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['limite_disponivel'] = $validated['limite'] ?? null;
        $validated['ativo'] = true;

        Card::create($validated);

        return redirect()->route('cards.index')->with('success', 'Cartão adicionado com sucesso.');
    }

    public function edit(Card $card)
    {
        $this->authorizeUser($card);

        return view('cards.edit', compact('card'));
    }

    public function update(Request $request, Card $card)
    {
        $this->authorizeUser($card);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'bandeira' => 'required|string|max:50',
            'ultimos_digitos' => 'required|string|size:4',
            'limite' => 'nullable|numeric|min:0',
            'fechamento_fatura' => 'nullable|integer|min:1|max:31',
            'vencimento_fatura' => 'nullable|integer|min:1|max:31',
        ]);

        // Garante que 'ativo' seja sempre definido (true ou false)
        $validated['ativo'] = $request->has('ativo');

        if (isset($validated['limite']) && $card->limite !== $validated['limite']) {
            $diferenca = $validated['limite'] - $card->limite;
            $validated['limite_disponivel'] = $card->limite_disponivel + $diferenca;
        }

        $card->update($validated);

        return redirect()->route('cards.index')->with('success', 'Cartão atualizado com sucesso.');
    }

    public function destroy(Card $card)
    {
        $this->authorizeUser($card);
        $card->delete();

        return redirect()->route('cards.index')->with('success', 'Cartão excluído com sucesso.');
    }

    private function authorizeUser(Card $card)
    {
        if ($card->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
