<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'card_id',
        'invoice_value',
        'invoice_expiration',
        'invoice_closure'
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }
}
