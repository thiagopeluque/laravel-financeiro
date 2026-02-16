<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Transação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                            <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione uma categoria</option>
                                @foreach($categories->groupBy('tipo') as $tipo => $cats)
                                    <optgroup label="{{ ucfirst($tipo) }}s">
                                        @foreach($cats as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->nome }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="valor" class="block text-sm font-medium text-gray-700">Valor</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                </div>
                                <input type="number" name="valor" id="valor" value="{{ old('valor', $transaction->valor) }}" required min="0.01" step="0.01" class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0,00">
                            </div>
                            @error('valor')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <input type="text" name="descricao" id="descricao" value="{{ old('descricao', $transaction->descricao) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ex: Salário mensal">
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="data" class="block text-sm font-medium text-gray-700">Data</label>
                            <input type="date" name="data" id="data" value="{{ old('data', $transaction->data->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('data')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                            <textarea name="observacoes" id="observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Informações adicionais...">{{ old('observacoes', $transaction->observacoes) }}</textarea>
                            @error('observacoes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(!$transaction->isRecurringChild())
                        <div class="mb-4 border-t pt-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="recorrente" id="recorrente" value="1" {{ old('recorrente', $transaction->recorrente) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="toggleRecorrente()">
                                <label for="recorrente" class="ml-2 block text-sm font-medium text-gray-700">
                                    Lançamento Recorrente
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Marque esta opção para repetir esta transação mensalmente</p>
                        </div>

                        <div id="recorrente-options" class="mb-4 {{ old('recorrente', $transaction->recorrente) ? '' : 'hidden' }}">
                            <label for="recorrente_ate" class="block text-sm font-medium text-gray-700">Até qual Mês / Ano</label>
                            <input type="month" name="recorrente_ate" id="recorrente_ate" value="{{ old('recorrente_ate', $transaction->recorrente_ate?->format('Y-m')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Escolha até qual mês a transação deve ser lançada automaticamente</p>
                            @error('recorrente_ate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @else
                        <div class="mb-4 border-t pt-4 bg-gray-50 p-3 rounded">
                            <p class="text-sm text-gray-600">
                                Esta é uma transação recorrente gerada automaticamente. 
                                <a href="{{ route('transactions.edit', $transaction->transacao_recorrente_pai_id) }}" class="text-indigo-600 hover:underline">Editar transação principal</a>
                            </p>
                        </div>
                        @endif

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleRecorrente() {
            const recorrenteCheckbox = document.getElementById('recorrente');
            const recorrenteOptions = document.getElementById('recorrente-options');
            if (recorrenteCheckbox.checked) {
                recorrenteOptions.classList.remove('hidden');
                const today = new Date();
                const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
                const month = nextMonth.getMonth() + 1;
                const year = nextMonth.getFullYear();
                const defaultDate = year + '-' + String(month).padStart(2, '0');
                if (!document.getElementById('recorrente_ate').value) {
                    document.getElementById('recorrente_ate').value = defaultDate;
                }
            } else {
                recorrenteOptions.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
