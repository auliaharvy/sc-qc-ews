<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bnf extends Model
{
    use HasFactory;

    protected $table = 'bad_news_first';

    protected $fillable = [
        'supplier_id',
        'part_id',
        'problem',
        'description',
        'qty',
        'status',
        'issuance_date',
        'completion_date',
        'created_by',
        'finish_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
