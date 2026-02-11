<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->mes ?? date('m');
        $ano = $request->ano ?? date('Y');

        $transactions = Auth::user()->transactions()
            ->with(['category', 'card'])
            ->whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->orderBy('data', 'desc')
            ->paginate(20);

        $totalReceitas = $transactions->where('category.tipo', 'receita')->sum('valor');
        $totalDespesas = $transactions->where('category.tipo', 'despesa')->sum('valor');
        $saldo = $totalReceitas - $totalDespesas;

        $resumoPorCategoria = Auth::user()->transactions()
            ->with('category')
            ->whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->get()
            ->groupBy('category.nome')
            ->map(function ($items) {
                return [
                    'tipo' => $items->first()->category->tipo,
                    'total' => $items->sum('valor'),
                ];
            });

        $categorias = Auth::user()->categories()->orderBy('nome')->get();

        $cards = Auth::user()->cards()->where('ativo', true)->orderBy('nome')->get();

        $gastosPorCartao = [];
        foreach ($cards as $card) {
            $gastosPorCartao[$card->id] = [
                'nome' => $card->nome,
                'icone' => $card->icone,
                'bandeira' => $card->bandeira,
                'ultimos_digitos' => $card->ultimos_digitos,
                'total' => Auth::user()->transactions()
                    ->where('card_id', $card->id)
                    ->whereMonth('data', $mes)
                    ->whereYear('data', $ano)
                    ->sum('valor'),
            ];
        }

        $mesAnterior = (int) $mes === 1 ? 12 : (int) $mes - 1;
        $anoAnterior = (int) $mes === 1 ? (int) $ano - 1 : (int) $ano;

        $comparacaoReceitas = Auth::user()->transactions()
            ->whereHas('category', function ($q) {
                $q->where('tipo', 'receita');
            })
            ->whereMonth('data', $mesAnterior)
            ->whereYear('data', $anoAnterior)
            ->sum('valor');

        $comparacaoDespesas = Auth::user()->transactions()
            ->whereHas('category', function ($q) {
                $q->where('tipo', 'despesa');
            })
            ->whereMonth('data', $mesAnterior)
            ->whereYear('data', $anoAnterior)
            ->sum('valor');

        $evolucaoMensal = Auth::user()->transactions()
            ->with('category')
            ->whereYear('data', $ano)
            ->orWhere(function ($q) use ($anoAnterior) {
                $q->whereYear('data', $anoAnterior)->whereMonth('data', 12);
            })
            ->get()
            ->groupBy(function ($item) {
                return $item->data->format('Y-m');
            })
            ->map(function ($items) {
                return [
                    'receitas' => $items->where('category.tipo', 'receita')->sum('valor'),
                    'despesas' => $items->where('category.tipo', 'despesa')->sum('valor'),
                ];
            })
            ->sortKeys();

        return view('dashboard', compact(
            'totalReceitas',
            'totalDespesas',
            'saldo',
            'transactions',
            'resumoPorCategoria',
            'mes',
            'ano',
            'categorias',
            'cards',
            'gastosPorCartao',
            'mesAnterior',
            'anoAnterior',
            'comparacaoReceitas',
            'comparacaoDespesas',
            'evolucaoMensal'
        ));
    }
}
