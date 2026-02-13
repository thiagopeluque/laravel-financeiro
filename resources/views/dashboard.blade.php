<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <button type="button" onclick="document.getElementById('modalNovaTransacao').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                + Nova Transação
            </button>
        </div>
    </x-slot>

    <div id="modalNovaTransacao" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto z-50">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <form action="{{ route('transactions.store') }}" method="POST">
                    @csrf
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Nova Transação</h3>

                    <div class="space-y-4 mb-4">
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                            <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione uma categoria</option>
                                @foreach($categorias->groupBy('tipo') as $tipo => $cats)
                                    <optgroup label="{{ ucfirst($tipo) }}s">
                                        @foreach($cats as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="card_id" class="block text-sm font-medium text-gray-700">Cartão (opcional)</label>
                            <select name="card_id" id="card_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" onchange="toggleParcelas()">
                                <option value="">À vista / Débito</option>
                                @foreach($cards as $card)
                                    <option value="{{ $card->id }}">{{ $card->icone }} {{ $card->nome }} •••• {{ $card->ultimos_digitos }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="parcelasContainer" class="mb-4 hidden">
                            <label for="parcelas" class="block text-sm font-medium text-gray-700">Número de Parcelas</label>
                            <select name="parcelas" id="parcelas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @for($i = 1; $i <= 24; $i++)
                                    <option value="{{ $i }}">{{ $i }}x {{ $i > 1 ? '(sem juros)' : '' }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="valor" class="block text-sm font-medium text-gray-700">Valor</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                </div>
                                <input type="number" step="0.01" name="valor" id="valor" required class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0,00">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <input type="text" name="descricao" id="descricao" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ex: Salário mensal">
                        </div>

                        <div class="mb-4">
                            <label for="data" class="block text-sm font-medium text-gray-700">Data</label>
                            <input type="date" name="data" id="data" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div class="mb-4">
                            <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                            <textarea name="observacoes" id="observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Informações adicionais..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mb-4">
                        <button type="button" onclick="document.getElementById('modalNovaTransacao').classList.add('hidden')" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleParcelas() {
            const cardSelect = document.getElementById('card_id');
            const parcelasContainer = document.getElementById('parcelasContainer');
            if (cardSelect.value) {
                parcelasContainer.classList.remove('hidden');
            } else {
                parcelasContainer.classList.add('hidden');
                document.getElementById('parcelas').value = 1;
            }
        }
    </script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" class="bg-white p-4 rounded-lg shadow sm:rounded-lg flex items-center justify-center gap-4">
                <div>
                    <label for="mes" class="block text-sm font-medium text-gray-700">Mês</label>
                    <select name="mes" id="mes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $mes == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $i)->locale('pt_BR')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="ano" class="block text-sm font-medium text-gray-700">Ano</label>
                    <select name="ano" id="ano" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @for($a = date('Y') - 1; $a <= date('Y') + 1; $a++)
                            <option value="{{ $a }}" {{ $ano == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endfor
                    </select>
                </div>
                <div class="pt-5">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filtrar
                    </button>
                </div>
            </form>

            <div class="grid grid-cols-3 gap-2 sm:gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Receitas</div>
                        <div class="text-xl sm:text-3xl font-bold text-green-600 mt-1 sm:mt-2">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Despesas</div>
                        <div class="text-xl sm:text-3xl font-bold text-red-600 mt-1 sm:mt-2">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Saldo</div>
                        <div class="text-xl sm:text-3xl font-bold {{ $saldo >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1 sm:mt-2">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            @if($comparacaoReceitas > 0 || $comparacaoDespesas > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-sm sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Mês Anterior</h3>
                        <div class="space-y-3 sm:space-y-4 text-sm sm:text-base">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Receitas</span>
                                <span class="font-semibold text-green-600">R$ {{ number_format($comparacaoReceitas, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Despesas</span>
                                <span class="font-semibold text-red-600">R$ {{ number_format($comparacaoDespesas, 2, ',', '.') }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Saldo</span>
                                <span class="font-bold {{ ($comparacaoReceitas - $comparacaoDespesas) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    R$ {{ number_format($comparacaoReceitas - $comparacaoDespesas, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-sm sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Variação</h3>
                        <div class="space-y-2 sm:space-y-3 text-sm sm:text-base">
                            @php
                                $variacaoReceitas = $comparacaoReceitas > 0 ? (($totalReceitas - $comparacaoReceitas) / $comparacaoReceitas) * 100 : 0;
                                $variacaoDespesas = $comparacaoDespesas > 0 ? (($totalDespesas - $comparacaoDespesas) / $comparacaoDespesas) * 100 : 0;
                            @endphp
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Receitas</span>
                                <span class="font-semibold {{ $variacaoReceitas >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $variacaoReceitas >= 0 ? '+' : '' }}{{ number_format($variacaoReceitas, 1, ',', '.') }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Despesas</span>
                                <span class="font-semibold {{ $variacaoDespesas <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $variacaoDespesas >= 0 ? '+' : '' }}{{ number_format($variacaoDespesas, 1, ',', '.') }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(count($evolucaoMensal) > 1)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-sm sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Evolução Mensal</h3>
                    <div class="overflow-x-auto -mx-4 sm:mx-0">
                        <table class="min-w-full text-xs sm:text-sm">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="text-left py-2 px-2 sm:px-4 font-medium text-gray-500">Mês</th>
                                    <th class="text-right py-2 px-2 sm:px-4 font-medium text-gray-500">Receitas</th>
                                    <th class="text-right py-2 px-2 sm:px-4 font-medium text-gray-500">Despesas</th>
                                    <th class="text-right py-2 px-2 sm:px-4 font-medium text-gray-500">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($evolucaoMensal as $key => $dados)
                                    @php
                                        $saldoMes = $dados['receitas'] - $dados['despesas'];
                                        $mesAno = \Carbon\Carbon::createFromFormat('Y-m', $key);
                                        $isCurrentMonth = $key === $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);
                                    @endphp
                                    <tr class="border-b {{ $isCurrentMonth ? 'bg-indigo-50' : '' }}">
                                        <td class="py-2 px-2 sm:px-4 {{ $isCurrentMonth ? 'font-semibold text-indigo-700' : 'text-gray-900' }}">
                                            {{ substr($mesAno->locale('pt_BR')->monthName, 0, 3) }} {{ $mesAno->format('Y') }}
                                        </td>
                                        <td class="py-2 px-2 sm:px-4 text-right text-green-600">R$ {{ number_format($dados['receitas'], 0, ',', '.') }}</td>
                                        <td class="py-2 px-2 sm:px-4 text-right text-red-600">R$ {{ number_format($dados['despesas'], 0, ',', '.') }}</td>
                                        <td class="py-2 px-2 sm:px-4 text-right font-semibold {{ $saldoMes >= 0 ? 'text-green-600' : 'text-red-600' }}">R$ {{ number_format($saldoMes, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Transações do Mês</h3>
                        @if($transactions->count() > 0)
                            <div class="space-y-2">
                                @foreach($transactions as $transacao)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900 flex items-center gap-2">
                                                {{ $transacao->descricao }}
                                                @if($transacao->total_parcelas && $transacao->total_parcelas > 1)
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                        {{ $transacao->parcela_atual }}/{{ $transacao->total_parcelas }}
                                                    </span>
                                                @endif
                                                <span class="text-gray-400">• {{ $transacao->data->format('d/m') }}</span>
                                            </div>
                                            <div class="text-sm text-gray-500 flex items-center gap-2 mt-1">
                                                {{ $transacao->category->nome }}
                                                @if($transacao->card)
                                                    <span class="text-indigo-600">{{ $transacao->card->nome }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="font-medium ml-4 {{ $transacao->category->tipo == 'receita' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transacao->category->tipo == 'receita' ? '+' : '-' }} R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $transactions->links() }}
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">Nenhuma transação neste período.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Resumo por Categoria</h3>
                        @if($resumoPorCategoria->count() > 0)
                            <div class="space-y-2">
                                @foreach($resumoPorCategoria->sortByDesc('total') as $categoria => $dados)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">{{ $categoria }}</span>
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $dados['tipo'] == 'receita' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ ucfirst($dados['tipo']) }}
                                            </span>
                                        </div>
                                        <div class="font-medium {{ $dados['tipo'] == 'receita' ? 'text-green-600' : 'text-red-600' }}">
                                            R$ {{ number_format($dados['total'], 2, ',', '.') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">Nenhuma transação neste período.</p>
                        @endif
                    </div>
                </div>
            </div>

            @if(count($gastosPorCartao) > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-sm sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Gastos por Cartão</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        @foreach($gastosPorCartao as $dados)
                            @if($dados['total'] > 0)
                                <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                                    <div class="flex items-center gap-2 sm:gap-3 mb-2">
                                        <span class="text-xl sm:text-2xl">{{ $dados['icone'] }}</span>
                                        <div class="min-w-0">
                                            <div class="font-medium text-gray-900 text-sm sm:text-base truncate">{{ $dados['nome'] }}</div>
                                            <div class="text-xs text-gray-500">•••• {{ $dados['ultimos_digitos'] }}</div>
                                        </div>
                                    </div>
                                    <div class="text-base sm:text-xl font-bold text-red-600">R$ {{ number_format($dados['total'], 2, ',', '.') }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
