<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestChangeDataDetail extends Model
{
    use HasFactory;

    protected $table = 'request_change_data_detail';

    protected $fillable = [
        'request_change_data_id',
        'ng_type_id',
        'daily_checksheet_id',
        'quantity',
    ];

    public function requestChangeData()
    {
        return $this->belongsTo(RequestChangeData::class, 'request_change_data_id');
    }

    public function ngType()
    {
        return $this->belongsTo(NgType::class, 'ng_type_id');
    }

    public function dailyChecksheet()
    {
        return $this->belongsTo(DailyChecksheet::class, 'daily_checksheet_id');
    }
}