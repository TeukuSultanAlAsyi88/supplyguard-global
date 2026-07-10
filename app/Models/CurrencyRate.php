<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'base_currency', 'target_currency', 'rate', 'change_percent', 'recorded_at', 'rate_date', 'source',
    ];

    protected $casts = [
        'rate' => 'float',
        'change_percent' => 'float',
        'recorded_at' => 'datetime',
        'rate_date' => 'date',
    ];
}
