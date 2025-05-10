<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\Part;
use App\Models\Supplier;
use App\Models\DailyChecksheet;
use App\Models\RequestChangeData;
use App\Models\RequestChangeDataDetail;
use App\Models\DailyChecksheetNg;
use App\Models\NgType;
use App\Services\BnfService;
use App\Services\ProblemListService;
use App\Models\Bnf;
use App\Models\ProblemList;

class RequestChangeDataService
{
    private $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    private $months = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember',
    ];

    public function dataTable()
    {
        $userRole = auth()->user()->roles()->first()->name;
        $supplierId = auth()->user()->supplier_id;

        if ($userRole == 'Admin Supplier') {
            $data = RequestChangeData::with(['supplier'])
            ->select([
                'supplier_id',
                'production_date',
                DB::raw('MAX(status) as status'),
                DB::raw('SUM(total_produced) as total_produced'),
                DB::raw('SUM(total_ng) as total_ng'),
                DB::raw('SUM(total_ok) as total_ok'),
                DB::raw('DATE(production_date) as formatted_date') // Menambahkan formatted_date
            ])
            ->where('supplier_id', $supplierId)
            ->groupBy('supplier_id', 'formatted_date', 'production_date') // Menggunakan formatted_date untuk groupBy
            ->orderByDesc('formatted_date', 'DESC')
            ->get();
        } else {
            $data = RequestChangeData::with(['supplier'])
            ->select([
                'supplier_id',
                'production_date',
                DB::raw('MAX(status) as status'),
                DB::raw('SUM(total_produced) as total_produced'),
                DB::raw('SUM(total_ng) as total_ng'),
                DB::raw('SUM(total_ok) as total_ok'),
                DB::raw('DATE(production_date) as formatted_date') // Menambahkan formatted_date
            ])
            ->groupBy('supplier_id', 'formatted_date', 'production_date')
            ->orderBy('production_date', 'DESC')
            ->get();
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('supplier_name', function($row) {
                return $row->supplier->name ?? '-';
            })
            ->addColumn('status', function($row) {
                return  $row->status == 'pending' ? '<span class="badge bg-primary ">Menunggu Persetujuan</span>' : '<span class="badge bg-danger">Ditolak</span>';

            })
            ->addColumn('formated_date', function($row) {
                if (!$row->production_date) {
                    return '-';
                }

                $date = \Carbon\Carbon::parse($row->production_date);
                $dayName = $this->days[$date->format('l')];
                $monthName = $this->months[$date->format('F')];

                return "$dayName, " . $date->format('d ') . $monthName . $date->format(' Y');
            })
            ->addColumn('ng_ratio', function($row) {
                $totalProduced = $row->total_produced;
                $totalNg = $row->total_ng;
                if ($totalProduced > 0) {
                    return number_format(($totalNg / $totalProduced) * 100, 2) . '%';
                } else {
                    return '0%';
                }
            })
            ->addColumn('oke_ratio', function($row) {
                $totalProduced = $row->total_produced;
                $totalOk = $row->total_ok;
                if ($totalProduced > 0) {
                    return number_format(($totalOk / $totalProduced) * 100, 2) . '%';
                } else {
                    return '0%';
                }
            })
            ->addColumn('action', function ($row) {
                $detailUrl = route('request-change-data.detail', ['supplier_id' => $row->supplier_id, 'production_date' => $row->production_date]);
                $role = auth()->user()->roles()->first()->name;

                if ($row->status === 'pending' && $role === 'admin') {
                    $buttonText = 'Konfirmasi';
                    $buttonClass = 'btn-primary';
                } else {
                    $buttonText = 'Detail';
                    $buttonClass = 'btn-warning';
                }
            
                $actionBtn = '<a href="' . $detailUrl . '" class="btn btn-sm me-2 ' . $buttonClass . '" title="' . $buttonText . '">' . $buttonText . '</a>';
                return '<div class="d-flex">' . $actionBtn . '</div>';
            })
            ->rawColumns(['action', 'ng_ratio', 'oke_ratio', 'status'])
            ->make(true);
    }

    public function getDetailBySupplierAndDate($supplier_id, $production_date)
    {
        // Mengambil detail dari DailyChecksheet berdasarkan supplier_id dan production_date
        $details = DailyChecksheet::where('supplier_id', $supplier_id)
            ->whereDate('production_date', $production_date)
            ->get()
            ->groupBy('part_id')
            ->map(function ($group) {
                return [
                    'id' => $group->first()->id,
                    'part_id' => $group->first()->part_id,
                    'part_name' => $group->first()->part->part_name ?? '-',
                    'shift' => $group->first()->shift ?? '-',
                    'total_produced' => $group->sum('total_produced'),
                    'total_ng' => $group->sum('total_ng'),
                    'total_ok' => $group->sum('total_ok'),
                ];
            });

        // Inisialisasi array untuk menyimpan hasil
        $result = [];

        foreach ($details as $detail) {
            $ngRatio = $detail['total_produced'] > 0 ? number_format(($detail['total_ng'] / $detail['total_produced']) * 100, 0) . '%' : '0%';
            $okeRatio = $detail['total_produced'] > 0 ? number_format(($detail['total_ok'] / $detail['total_produced']) * 100, 0) . '%' : '0%';
            $ngRatioNumber = $detail['total_produced'] > 0 ? ($detail['total_ng'] / $detail['total_produced']) * 100 : 0;
            $okeRatioNumber = $detail['total_produced'] > 0 ? ($detail['total_ok'] / $detail['total_produced']) * 100 : 0;
            $result[] = [
                'id' => $detail['id'],
                'part_id' => $detail['part_id'],
                'part_name' => $detail['part_name'] ?? '-',
                'shift' => $detail['shift'] ?? '-',
                'total_produced' => $detail['total_produced'],
                'total_ng' => $detail['total_ng'],
                'total_ok' => $detail['total_ok'],
                'ng_ratio_number' => $ngRatioNumber,
                'ng_ratio' => $ngRatio,
                'oke_ratio' => $okeRatio,
                'oke_ratio_number' => $okeRatioNumber,
            ];
        }

        // Mengembalikan respons JSON dari data
        return $result;
    }


    public function getDetailRequestChangeBySupplierAndDate($supplier_id, $production_date)
    {
        // Mengambil detail dari DailyChecksheet berdasarkan supplier_id dan production_date
        $details = RequestChangeData::where('supplier_id', $supplier_id)
            ->whereDate('production_date', $production_date)
            ->get()
            ->groupBy('part_id')
            ->map(function ($group) {
                return [
                    'id' => $group->first()->id,
                    'daily_checksheet_id' => $group->first()->daily_checksheet_id,
                    'status' => $group->first()->status,
                    'part_id' => $group->first()->part_id,
                    'part_name' => $group->first()->part->part_name ?? '-',
                    'part_number' => $group->first()->part->part_number ?? '-',
                    'total_produced' => $group->sum('total_produced'),
                    'total_ng' => $group->sum('total_ng'),
                    'total_ok' => $group->sum('total_ok'),
                ];
            });

        // Inisialisasi array untuk menyimpan hasil
        $result = [];

        foreach ($details as $detail) {

            $ngRatio = $detail['total_produced'] > 0 ? number_format(($detail['total_ng'] / $detail['total_produced']) * 100, 0) . '%' : '0%';
            $okeRatio = $detail['total_produced'] > 0 ? number_format(($detail['total_ok'] / $detail['total_produced']) * 100, 0) . '%' : '0%';
            $ngRatioNumber = $detail['total_produced'] > 0 ? ($detail['total_ng'] / $detail['total_produced']) * 100 : 0;
            $okeRatioNumber = $detail['total_produced'] > 0 ? ($detail['total_ok'] / $detail['total_produced']) * 100 : 0;
            $result[] = [
                'id' => $detail['id'],
                'daily_checksheet_id' => $detail['daily_checksheet_id'],
                'part_id' => $detail['part_id'],
                'part_name' => $detail['part_name'] ?? '-',
                'part_number' => $detail['part_number'] ?? '-',
                'total_produced' => $detail['total_produced'],
                'total_ng' => $detail['total_ng'],
                'total_ok' => $detail['total_ok'],
                'ng_ratio_number' => $ngRatioNumber,
                'ng_ratio' => $ngRatio,
                'oke_ratio' => $okeRatio,
                'oke_ratio_number' => $okeRatioNumber,
                'status' => $detail['status'],
            ];
        }

        // Mengembalikan respons JSON dari data
        return $result;
    }


    public function getListPart($supplier_id )
    {
        $parts = Part::where('supplier_id', $supplier_id)->get();

        return $parts;
    }


    public function getTitle($supplier_id, $production_date)
    {
        $supplier = Supplier::findById($supplier_id);

        $date = \Carbon\Carbon::parse($production_date);
        $dayName = $this->days[$date->format('l')];
        $monthName = $this->months[$date->format('F')];
        $formattedDate = "$dayName, " . $date->format('d ') . $monthName . $date->format(' Y');

        return $formattedDate;
    }

    public function getById($id)
    {
        return Part::with('supplier')->findOrFail($id);
    }

    public function create($data)
    {
        // return $data;
        DB::beginTransaction();
        $supplier_id = $data[0]['supplier_id'];
        $production_date = $data[0]['production_date'];
        try {
            foreach ($data as $item) {
                if($item['total_produced'] != 0) {
                    $requestDataChange = RequestChangeData::create([
                        'daily_checksheet_id' => $item['daily_checksheet_id'],
                        'supplier_id' => $item['supplier_id'],
                        'part_id' => $item['part_id'],
                        'production_date' => $item['production_date'],
                        'shift' => $item['shift'] ,
                        'total_produced' => $item['total_produced'] ?? 0,
                        'total_ng' => $item['ng'] ?? 0,
                        'total_ok' => $item['good'] ?? 0,
                        'created_at' => now()->setTimezone('Asia/Jakarta'),
                        'updated_at' => now()->setTimezone('Asia/Jakarta'),
                    ]);

                    foreach ($item['ng_types'] as $ngType) {
                        $dailyChecksheetNg = RequestChangeDataDetail::create([
                            'request_change_data_id' => $requestDataChange['id'], // pastikan variabel ini tersedia di konteks
                            'ng_type_id' => $ngType['id'],
                            'daily_checksheet_id' => $item['daily_checksheet_id'],
                            'quantity' => $ngType['quantity'] ?? 0,
                        ]);
                    }

                    
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Request Change Data berhasil diajukan',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menambahkan Request Change Data: ' . $e->getMessage()
            ];
        }
    }

    
    public function updateRequestChange($data)
    {
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                if (!isset($item['daily_checksheet_id']) || !isset($item['total_produced'])) {
                    continue; // skip item jika ID atau data utama tidak lengkap
                }
    
                // Update DailyChecksheet
                if($item['total_produced'] != 0) {
                    $dailyChecksheet = DailyChecksheet::findOrFail($item['daily_checksheet_id']);
                
                    if ($dailyChecksheet) {
                        $dailyChecksheet->update([
                            'total_produced' => $item['total_produced'] ?? $dailyChecksheet->total_produced,
                            'total_ng' => $item['ng'] ?? $dailyChecksheet->total_ng,
                            'total_ok' => $item['good'] ?? $dailyChecksheet->total_ok,
                            'updated_at' => now()->setTimezone('Asia/Jakarta'),
                        ]);
                    }
                    
                    if (isset($item['ng_types']) && is_array($item['ng_types']) && isset($item['daily_checksheet_id'])) {
                        foreach ($item['ng_types'] as $ngType) {
                            if (
                                isset($ngType['id']) &&
                                isset($ngType['quantity'])
                            ) {
                                // Use DB::table instead of the model to debug the issue
                                DB::table('daily_checksheet_ng')
                                    ->where('daily_checksheet_id', $item['daily_checksheet_id'])
                                    ->where('ng_type_id', $ngType['id'])
                                    ->update([
                                        'quantity' => $ngType['quantity'],
                                        'updated_at' => now()->setTimezone('Asia/Jakarta')
                                    ]);
                                    
                                
                            }
                        }
                    }

                    if (isset($item['request_change_data_id'])) {
                        // First delete related detail records
                        RequestChangeDataDetail::where('request_change_data_id', $item['request_change_data_id'])->delete();
                        
                        // Then delete the main record
                        RequestChangeData::where('id', $item['request_change_data_id'])->delete();
                        
                    }
                }
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Data berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ];
        }
    }

    public function rejectRequestChange($data){
        DB::beginTransaction();
        try {
            foreach ($data as $id) {
                if (empty($id)) {
                    continue;
                }
    
                $requestChange = RequestChangeData::find($id);
    
                if ($requestChange) {
                    $requestChange->update([
                        'status' => 'reject',
                    ]);
                }
            }
    
            DB::commit();
    
            return [
                'success' => true,
                'message' => 'Request Change Data Ditolak',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menolak Request Change Data: ' . $e->getMessage()
            ];
        }
    }
    
}
