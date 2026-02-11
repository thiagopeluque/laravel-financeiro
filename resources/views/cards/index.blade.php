<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Meus Cartões') }}
            </h2>
            <a href="{{ route('cards.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                + Novo Cartão
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if($cards->count() > 0)
                @foreach($cards as $card)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-3xl">
                                        {{ $card->icone }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $card->nome }}</h3>
                                        <p class="text-sm text-gray-500">{{ ucfirst($card->bandeira) }} •••• {{ $card->ultimos_digitos }}</p>
                                        @if($card->fechamento_fatura && $card->vencimento_fatura)
                                            <p class="text-xs text-gray-400 mt-1">Fechamento: dia {{ $card->fechamento_fatura }} • Vencimento: dia {{ $card->vencimento_fatura }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    @if($card->limite)
                                        <div class="text-right mr-4">
                                            <div class="text-sm text-gray-500">Limite</div>
                                            <div class="font-medium">R$ {{ number_format($card->limite, 2, ',', '.') }}</div>
                                        </div>
                                    @endif
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $card->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $card->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>

                            @php
                                $transacoesCartao = \App\Models\Transaction::where('card_id', $card->id)
                                    ->whereMonth('data', date('m'))
                                    ->whereYear('data', date('Y'))
                                    ->with('category')
                                    ->orderBy('data', 'desc')
                                    ->get();
                                $totalGastos = $transacoesCartao->sum('valor');
                            @endphp

                            @if($totalGastos > 0)
                                <div class="bg-red-50 rounded-lg p-4 mb-4">
                                    <div class="text-sm text-red-600">Total gasto este mês</div>
                                    <div class="text-2xl font-bold text-red-700">R$ {{ number_format($totalGastos, 2, ',', '.') }}</div>
                                </div>

                                @if($transacoesCartao->count() > 0)
                                    <div class="border-t border-gray-200 pt-4">
                                        <h4 class="font-medium text-gray-900 mb-3">Últimas Compras</h4>
                                        <div class="space-y-2">
                                            @foreach($transacoesCartao->take(10) as $transacao)
                                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <div class="font-medium text-gray-900">{{ $transacao->descricao }}</div>
                                                        <div class="text-sm text-gray-500">{{ $transacao->category->nome }} • {{ $transacao->data->format('d/m/Y') }}</div>
                                                    </div>
                                                    <div class="font-medium text-red-600">
                                                        - R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($transacoesCartao->count() > 10)
                                            <div class="mt-3 text-center">
                                                <span class="text-sm text-gray-500">e mais {{ $transacoesCartao->count() - 10 }} transações...</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="bg-gray-50 rounded-lg p-4 text-center">
                                    <p class="text-gray-500">Nenhuma compra este mês</p>
                                </div>
                            @endif

                            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('cards.edit', $card) }}" class="inline-flex items-center px-3 py-1 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                    Editar
                                </a>
                                <form action="{{ route('cards.destroy', $card) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700" onclick="return confirm('Tem certeza que deseja excluir este cartão?')">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div>
                    {{ $cards->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-gray-500 mb-4">Nenhum cartão cadastrado.</p>
                        <a href="{{ route('cards.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Adicionar Cartão
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
