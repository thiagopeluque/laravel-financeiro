<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configuração Telegram') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Integração Telegram') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Configure o bot do Telegram para registrar transações via mensagens.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('profile.telegram.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="telegram_enabled" :value="__('Ativar integração Telegram')" />
                                <div class="mt-2">
                                    <input type="checkbox" id="telegram_enabled" name="telegram_enabled" value="1" 
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        {{ old('telegram_enabled', $user->telegram_enabled) ? 'checked' : '' }}>
                                    <label for="telegram_enabled" class="ml-2 text-sm text-gray-600">
                                        {{ __('Habilitar registro de transações via Telegram') }}
                                    </label>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('telegram_enabled')" />
                            </div>

                            <div>
                                <x-input-label for="telegram_chat_id" :value="__('Chat ID')" />
                                <x-text-input id="telegram_chat_id" name="telegram_chat_id" type="text" class="mt-1 block w-full" 
                                    :value="old('telegram_chat_id', $user->telegram_chat_id)" autocomplete="off" />
                                <x-input-error class="mt-2" :messages="$errors->get('telegram_chat_id')" />
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ __('Para obter seu Chat ID, envie uma mensagem para @userinfobot no Telegram ou acesse https://api.telegram.org/bot<SEU_TOKEN>/getUpdates') }}
                                </p>
                            </div>

                            <div>
                                <x-input-label for="telegram_default_category_id" :value="__('Categoria padrão')" />
                                <select id="telegram_default_category_id" name="telegram_default_category_id" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('-- Selecione uma categoria --') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                            {{ old('telegram_default_category_id', $user->telegram_default_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->nome }} ({{ $category->tipo }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('telegram_default_category_id')" />
                            </div>

                            <div>
                                <x-input-label for="telegram_default_card_id" :value="__('Cartão padrão')" />
                                <select id="telegram_default_card_id" name="telegram_default_card_id" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('-- Selecione um cartão --') }}</option>
                                    @foreach($cards as $card)
                                        <option value="{{ $card->id }}" 
                                            {{ old('telegram_default_card_id', $user->telegram_default_card_id) == $card->id ? 'selected' : '' }}>
                                            {{ $card->nome }} ({{ $card->bandeira }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('telegram_default_card_id')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Salvar') }}</x-primary-button>

                                @if (session('status') === 'telegram-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600"
                                    >{{ __('Salvo.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Como usar') }}
                            </h2>
                        </header>

                        <div class="mt-4 text-sm text-gray-600 space-y-4">
                            <p><strong>{{ __('Exemplos de mensagens:') }}</strong></p>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li><code class="bg-gray-100 px-2 py-1 rounded">150 almoço no restaurante</code> - {{ __('Registra uma despesa de R$ 150,00') }}</li>
                                <li><code class="bg-gray-100 px-2 py-1 rounded">recebi 5000 salário</code> - {{ __('Registra uma receita de R$ 5.000,00') }}</li>
                                <li><code class="bg-gray-100 px-2 py-1 rounded">45 uber para casa</code> - {{ __('Registra uma despesa de R$ 45,00') }}</li>
                                <li><code class="bg-gray-100 px-2 py-1 rounded">recebi 1200 freelance projeto</code> - {{ __('Registra uma receita de R$ 1.200,00') }}</li>
                            </ul>

                            <p class="mt-4"><strong>{{ __('Regras:') }}</strong></p>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>{{ __('Use "recebi" no início para registrar uma receita') }}</li>
                                <li>{{ __('Sem "recebi", será registrado como despesa') }}</li>
                                <li>{{ __('O valor pode ser com ou sem casas decimais') }}</li>
                                <li>{{ __('A categoria será detectada automaticamente ou criada se não existir') }}</li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
