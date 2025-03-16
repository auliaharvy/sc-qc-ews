<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DailyChecksheetNg extends Pivot
{
    protected $table = 'daily_checksheet_ng';

    protected $fillable = [
        'daily_checksheet_id',
        'ng_type_id',
        'quantity'
    ];
}
