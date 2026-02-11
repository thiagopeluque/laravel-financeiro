<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'card_id',
        'valor',
        'descricao',
        'data',
        'observacoes',
        'total_parcelas',
        'parcela_atual',
        'transacao_pai_id',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'date',
        'total_parcelas' => 'integer',
        'parcela_atual' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transacao_pai_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transacao_pai_id');
    }

    public function isInstallment(): bool
    {
        return $this->total_parcelas !== null && $this->total_parcelas > 1;
    }

    public function getInstallmentDescription(): string
    {
        if ($this->total_parcelas && $this->total_parcelas > 1) {
            return "{$this->parcela_atual}/{$this->total_parcelas}";
        }

        return '';
    }
}
