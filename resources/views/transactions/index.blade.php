<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transações') }}
            </h2>
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Nova Transação
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" class="bg-white p-4 rounded-lg shadow sm:rounded-lg flex flex-wrap items-end gap-4">
                <div>
                    <label for="mes" class="block text-sm font-medium text-gray-700">Mês</label>
                    <select name="mes" id="mes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ request('mes', date('m')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $i)->locale('pt_BR')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select name="tipo" id="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos</option>
                        <option value="receita" {{ request('tipo') == 'receita' ? 'selected' : '' }}>Receitas</option>
                        <option value="despesa" {{ request('tipo') == 'despesa' ? 'selected' : '' }}>Despesas</option>
                    </select>
                </div>
                <div class="pt-5">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filtrar
                    </button>
                </div>
            </form>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if($transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $transaction->data->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $transaction->descricao }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $transaction->category->nome }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $transaction->category->tipo == 'receita' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->category->tipo == 'receita' ? '+' : '-' }} R$ {{ number_format($transaction->valor, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir esta transação?')">
                                                    Excluir
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        Nenhuma transação encontrada.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
