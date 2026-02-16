<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->index();
            $table->boolean('telegram_enabled')->default(false);
            $table->foreignId('telegram_default_category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('telegram_default_card_id')->nullable()->constrained('cards')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['telegram_chat_id']);
            $table->dropColumn('telegram_chat_id');
            $table->dropColumn('telegram_enabled');
            $table->dropForeign(['telegram_default_category_id']);
            $table->dropColumn('telegram_default_category_id');
            $table->dropForeign(['telegram_default_card_id']);
            $table->dropColumn('telegram_default_card_id');
        });
    }
};
