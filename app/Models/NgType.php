<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NgType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function checksheets()
    {
        return $this->belongsToMany(DailyChecksheet::class, 'daily_checksheet_ng')
                    ->withPivot('quantity');
    }
}
