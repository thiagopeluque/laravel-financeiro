<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->integer('fechamento_fatura')->nullable()->after('limite_disponivel');
            $table->integer('vencimento_fatura')->nullable()->after('fechamento_fatura');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['fechamento_fatura', 'vencimento_fatura']);
        });
    }
};
