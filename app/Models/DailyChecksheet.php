<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyChecksheet extends Model
{
    use HasFactory;

    protected $table = 'daily_checksheet';

    protected $fillable = [
        'part_id',
        'supplier_id',
        'production_date',
        'total_produced',
        'shift',
        'total_ok',
        'total_ng',
        'created_at',
        'updated_at'
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ngTypes()
    {
        return $this->belongsToMany(NgType::class, 'daily_checksheet_ng')
                    ->withPivot('quantity');
    }
}
