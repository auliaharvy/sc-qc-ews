<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'pic'
    ];

    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public function bnf()
    {
        return $this->hasMany(Bnf::class);
    }

    public function problem()
    {
        return $this->hasMany(ProblemList::class);
    }

    public function checksheets()
    {
        return $this->hasMany(DailyChecksheet::class);
    }

    public function performances()
    {
        return $this->hasMany(MonthlyPerformance::class);
    }

    public function routeNotificationForMail()
    {
        return $this->email; // Assuming 'email' is the column name in your suppliers table
    }
}
