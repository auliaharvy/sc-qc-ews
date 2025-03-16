<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'month',
        'ok_ratio',
        'ng_ratio'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
