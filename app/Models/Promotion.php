<?php

namespace App\Models;

use App\Models\Giveaway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends Model
{
    public function giveaway(): BelongsTo
    {
        return $this->belongsTo(Giveaway::class);
    }

    use HasFactory;

    protected $fillable = [
        'promotion_type',
        'cashback',
        'giveaway_id',
        'discount',
        'active',
        'start_date',
        'end_date',
    ];
}
