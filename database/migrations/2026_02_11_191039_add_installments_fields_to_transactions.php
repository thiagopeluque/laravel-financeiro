<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('total_parcelas')->nullable()->default(1)->after('card_id');
            $table->integer('parcela_atual')->nullable()->default(1)->after('total_parcelas');
            $table->foreignId('transacao_pai_id')->nullable()->constrained('transactions')->nullOnDelete()->after('parcela_atual');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['transacao_pai_id']);
            $table->dropColumn(['total_parcelas', 'parcela_atual', 'transacao_pai_id']);
        });
    }
};
