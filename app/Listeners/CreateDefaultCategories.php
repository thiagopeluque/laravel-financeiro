<?php

namespace App\Listeners;

use App\Models\Category;
use Illuminate\Auth\Events\Registered;

class CreateDefaultCategories
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        $categories = [
            // Despesas
            ['nome' => 'Aluguel', 'tipo' => 'despesa'],
            ['nome' => 'Condomínio', 'tipo' => 'despesa'],
            ['nome' => 'Água', 'tipo' => 'despesa'],
            ['nome' => 'Energia Elétrica', 'tipo' => 'despesa'],
            ['nome' => 'Gás', 'tipo' => 'despesa'],
            ['nome' => 'Internet', 'tipo' => 'despesa'],
            ['nome' => 'Supermercado', 'tipo' => 'despesa'],
            ['nome' => 'Feira', 'tipo' => 'despesa'],
            ['nome' => 'Restaurantes', 'tipo' => 'despesa'],
            ['nome' => 'Lanches / Delivery', 'tipo' => 'despesa'],
            ['nome' => 'Cafés', 'tipo' => 'despesa'],
            ['nome' => 'Combustível', 'tipo' => 'despesa'],
            ['nome' => 'Transporte Público', 'tipo' => 'despesa'],
            ['nome' => 'Aplicativos de Transporte', 'tipo' => 'despesa'],
            ['nome' => 'Estacionamento', 'tipo' => 'despesa'],
            ['nome' => 'Plano de Saúde', 'tipo' => 'despesa'],
            ['nome' => 'Medicamentos', 'tipo' => 'despesa'],
            ['nome' => 'Consultas Médicas', 'tipo' => 'despesa'],
            ['nome' => 'Dentista', 'tipo' => 'despesa'],
            ['nome' => 'Mensalidade Escolar', 'tipo' => 'despesa'],
            ['nome' => 'Cursos', 'tipo' => 'despesa'],
            ['nome' => 'Streaming', 'tipo' => 'despesa'],
            ['nome' => 'Cinema', 'tipo' => 'despesa'],
            ['nome' => 'Viagens', 'tipo' => 'despesa'],
            ['nome' => 'Roupas', 'tipo' => 'despesa'],
            ['nome' => 'Calçados', 'tipo' => 'despesa'],
            ['nome' => 'Cosméticos', 'tipo' => 'despesa'],
            ['nome' => 'Cabeleireiro / Barbeiro', 'tipo' => 'despesa'],
            ['nome' => 'Ração Pet', 'tipo' => 'despesa'],
            ['nome' => 'Veterinário', 'tipo' => 'despesa'],

            // Receitas
            ['nome' => 'Salário', 'tipo' => 'receita'],
            ['nome' => '13º Salário', 'tipo' => 'receita'],
            ['nome' => 'Freelance', 'tipo' => 'receita'],
            ['nome' => 'Comissões', 'tipo' => 'receita'],
            ['nome' => 'Bicos', 'tipo' => 'receita'],
            ['nome' => 'Aluguel Recebido', 'tipo' => 'receita'],
            ['nome' => 'Dividendos', 'tipo' => 'receita'],
            ['nome' => 'Rendimentos', 'tipo' => 'receita'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'user_id' => $user->id,
                'nome' => $category['nome'],
                'tipo' => $category['tipo']
            ]);
        }
    }
}
