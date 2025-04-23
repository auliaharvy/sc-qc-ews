<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionStatement extends Model
{
    protected $fillable = [
        'supplier_id',
        'date',
        'status',
        'reason',
        'notification_sent'
    ];

    protected $casts = [
        'date' => 'date',
        'notification_sent' => 'boolean'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}