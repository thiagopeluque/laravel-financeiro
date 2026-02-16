<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('recorrente')->default(false)->after('observacoes');
            $table->date('recorrente_ate')->nullable()->after('recorrente');
            $table->foreignId('transacao_recorrente_pai_id')->nullable()->after('transacao_pai_id')->constrained('transactions')->onDelete('cascade');
            $table->boolean('recorrente_ativa')->default(true)->after('recorrente_ate');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['transacao_recorrente_pai_id']);
            $table->dropColumn(['recorrente', 'recorrente_ate', 'transacao_recorrente_pai_id', 'recorrente_ativa']);
        });
    }
};
