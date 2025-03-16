<?php
// TODO: add dialog penambahan a3 report dan car, car untuk login sebagai sugity a3 sebagai supplier
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemList extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_date',
        'part_id',
        'supplier_id',
        'problem_description',
        'quantity_affected',
        'finding_location',
        'car_file',
        'no_car',
        'a3_report',
        'no_a3_report',
        'car_upload_at',
        'report_upload_at',
        'created_by',
        'updated_by',
        'status'
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
