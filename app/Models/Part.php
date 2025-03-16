<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'part_number',
        'part_name',
        'model',
        'sebango'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bnf()
    {
        return $this->hasMany(Bnf::class);
    }

    public function checksheets()
    {
        return $this->hasMany(DailyChecksheet::class);
    }

    public function problems()
    {
        return $this->hasMany(ProblemList::class);
    }
}
