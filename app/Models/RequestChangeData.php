<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestChangeData extends Model
{
    use HasFactory;

    protected $table = 'request_change_data';

    protected $fillable = [
        'daily_checksheet_id',
        'supplier_id',
        'part_id',
        'production_date',
        'shift',
        'total_produced',
        'total_ok',
        'total_ng',
    ];

    public function details()
    {
        return $this->hasMany(RequestChangeDataDetail::class, 'request_change_data_id');
    }

    public function dailyChecksheet()
    {
        return $this->belongsTo(DailyChecksheet::class, 'daily_checksheet_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function part()
    {
        return $this->belongsTo(Part::class, 'part_id');
    }
}