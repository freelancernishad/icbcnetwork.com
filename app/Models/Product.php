<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'daily_percentage', 'total_percentage', 'daily_income', 'total_earnings', 'days', 'price', 'cashback',
    ];

    public function purchases()
    {
        return $this->hasMany(UserProduct::class);
    }
}
