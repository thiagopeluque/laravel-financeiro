<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Novo Cartão') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('cards.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="nome" class="block text-sm font-medium text-gray-700">Nome do Cartão</label>
                            <input type="text" name="nome" id="nome" value="{{ old('nome') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ex: Nubank">
                            @error('nome')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="bandeira" class="block text-sm font-medium text-gray-700">Bandeira</label>
                            <select name="bandeira" id="bandeira" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione a bandeira</option>
                                <option value="visa" {{ old('bandeira') == 'visa' ? 'selected' : '' }}>Visa</option>
                                <option value="mastercard" {{ old('bandeira') == 'mastercard' ? 'selected' : '' }}>Mastercard</option>
                                <option value="elo" {{ old('bandeira') == 'elo' ? 'selected' : '' }}>Elo</option>
                                <option value="amex" {{ old('bandeira') == 'amex' ? 'selected' : '' }}>American Express</option>
                                <option value="hipercard" {{ old('bandeira') == 'hipercard' ? 'selected' : '' }}>Hipercard</option>
                                <option value="discover" {{ old('bandeira') == 'discover' ? 'selected' : '' }}>Discover</option>
                                <option value="jcb" {{ old('bandeira') == 'jcb' ? 'selected' : '' }}>JCB</option>
                                <option value="aura" {{ old('bandeira') == 'aura' ? 'selected' : '' }}>Aura</option>
                            </select>
                            @error('bandeira')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="ultimos_digitos" class="block text-sm font-medium text-gray-700">Últimos 4 Dígitos</label>
                            <input type="text" name="ultimos_digitos" id="ultimos_digitos" value="{{ old('ultimos_digitos') }}" required maxlength="4" pattern="[0-9]{4}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="1234">
                            @error('ultimos_digitos')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="limite" class="block text-sm font-medium text-gray-700">Limite (opcional)</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                </div>
                                <input type="number" name="limite" id="limite" value="{{ old('limite') }}" min="0" step="0.01" class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0,00">
                            </div>
                            @error('limite')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="fechamento_fatura" class="block text-sm font-medium text-gray-700">Fechamento da Fatura</label>
                                <input type="number" name="fechamento_fatura" id="fechamento_fatura" value="{{ old('fechamento_fatura') }}" min="1" max="31" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Dia 08">
                                <p class="mt-1 text-xs text-gray-500">Compras após esta data vão para a próxima fatura</p>
                                @error('fechamento_fatura')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="vencimento_fatura" class="block text-sm font-medium text-gray-700">Vencimento da Fatura</label>
                                <input type="number" name="vencimento_fatura" id="vencimento_fatura" value="{{ old('vencimento_fatura') }}" min="1" max="31" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Dia 15">
                                <p class="mt-1 text-xs text-gray-500">Data de pagamento da fatura</p>
                                @error('vencimento_fatura')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('cards.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
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
</x-app-layout>
