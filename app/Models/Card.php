<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $fillable = [
        'user_id',
        'nome',
        'bandeira',
        'ultimos_digitos',
        'limite',
        'limite_disponivel',
        'fechamento_fatura',
        'vencimento_fatura',
        'ativo',
    ];

    protected $casts = [
        'limite' => 'decimal:2',
        'limite_disponivel' => 'decimal:2',
        'fechamento_fatura' => 'integer',
        'vencimento_fatura' => 'integer',
        'ativo' => 'boolean',
    ];

    protected $appends = ['icone'];

    public function getIconeAttribute(): string
    {
        return match ($this->bandeira) {
            'visa' => '💳',
            'mastercard' => '💳',
            'elo' => '💳',
            'amex' => '💳',
            'hipercard' => '💳',
            'discover' => '💳',
            'jcb' => '💳',
            'aura' => '💳',
            default => '💳',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
