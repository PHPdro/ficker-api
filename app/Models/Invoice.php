<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

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
}
