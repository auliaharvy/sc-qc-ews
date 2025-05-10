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
use App\Models\DailyChecksheetNg;
use App\Models\NgType;
use App\Services\BnfService;
use App\Services\ProblemListService;
use App\Models\Bnf;
use App\Models\ProblemList;

class DailyChecksheetService
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
            $data = DailyChecksheet::with(['supplier'])
            ->select([
                'supplier_id',
                'production_date',
                DB::raw('SUM(total_produced) as total_produced'),
                DB::raw('SUM(total_ng) as total_ng'),
                DB::raw('SUM(total_ok) as total_ok'),
                DB::raw('DATE(production_date) as formatted_date') // Menambahkan formatted_date
            ])
            ->where('supplier_id', $supplierId)
            ->groupBy('supplier_id', 'formatted_date', 'production_date') // Menggunakan formatted_date untuk groupBy
            ->orderBy('production_date', 'DESC')
            ->get();
        } else {
            $data = DailyChecksheet::with(['supplier'])
            ->select([
                'supplier_id',
                'production_date',
                DB::raw('SUM(total_produced) as total_produced'),
                DB::raw('SUM(total_ng) as total_ng'),
                DB::raw('SUM(total_ok) as total_ok'),
                DB::raw('DATE(production_date) as formatted_date') // Menambahkan formatted_date
            ])
            ->groupBy('supplier_id', 'formatted_date', 'production_date') // Menggunakan formatted_date untuk groupBy
            ->orderBy('production_date', 'DESC')
            ->get();
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('supplier_name', function($row) {
                return $row->supplier->name ?? '-';
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
                $detailUrl = route('daily-check-sheet.detail', ['supplier_id' => $row->supplier_id, 'production_date' => $row->production_date]);
                $editUrl = route('request-change-data.create', ['supplier_id' => $row->supplier_id, 'production_date' => $row->production_date]);
                $actionBtn = '<a href="' . $detailUrl . '" class="btn btn-warning btn-sm me-2" title="Detail"><i class="fa fa-eye"></i></a>';
                if(auth()->user()->roles->contains('name', 'Admin Supplier')){
                    $actionBtn .= '<a href="' . $editUrl . '" class="btn btn-primary btn-sm" title="Edit"><i class="fa fa-edit"></i></a>';
                }
                return '<div class="d-flex">' . $actionBtn . '</div>';
            })
            ->rawColumns(['action', 'ng_ratio', 'oke_ratio'])
            ->make(true);
    }

    public function dataDashboardQuality($production_date)
    {
        $userRole = auth()->user()->roles()->first()->name;
        $supplierId = auth()->user()->supplier_id;

        if ($userRole == 'Admin Supplier') {
            $data = DailyChecksheet::with(['supplier'])
            ->select([
                'supplier_id',
                'production_date',
                DB::raw('SUM(total_produced) as total_produced'),
                DB::raw('SUM(total_ng) as total_ng'),
                DB::raw('SUM(total_ok) as total_ok'),
                DB::raw('DATE(production_date) as formatted_date'), // Menambahkan formatted_date
                DB::raw('(SELECT part_id
                      FROM daily_checksheet d2
                      WHERE d2.supplier_id = daily_checksheet.supplier_id
                        AND d2.production_date = daily_checksheet.production_date
                      GROUP BY d2.part_id
                      ORDER BY SUM(d2.total_ng) DESC
                      LIMIT 1) as top_part_id')
            ])
            ->where('supplier_id', $supplierId)
            ->groupBy('supplier_id', 'formatted_date', 'production_date') // Menggunakan formatted_date untuk groupBy
            ->where('production_date', $production_date)
            ->get();
        } else {
            $data = [];
            $suppliers = Supplier::get()->all();

            foreach ($suppliers as $supplier) {
                // Get daily checksheet data grouped by part with aggregates
                $checksheetData = DailyChecksheet::where('supplier_id', $supplier->id)
                    ->where('production_date', $production_date)
                    ->select(
                        'part_id',
                        DB::raw('SUM(total_produced) as total_produced'),
                        DB::raw('SUM(total_ng) as total_ng'),
                        DB::raw('SUM(total_ok) as total_ok')
                    )
                    ->groupBy('part_id')
                    ->orderByDesc('total_ng')
                    ->first();

                $part = $checksheetData && $checksheetData->part_id ? Part::find($checksheetData->part_id) : null;

                if ($checksheetData) {
                    $topProblemName = '-';
                    $topProblemQuantity = 0;

                    // Only check for problems if there's NG
                    if ($checksheetData->total_ng > 0) {
                        // Get the checksheet with the highest NG count for this specific supplier
                        $problemChecksheetData = DailyChecksheet::where('supplier_id', $supplier->id)
                            ->where('production_date', $production_date)
                            ->where('total_ng', '>', 0)
                            ->select('id', 'part_id')
                            ->orderByDesc('total_ng')
                            ->first();

                        if ($problemChecksheetData) {
                            $topProblem = DailyChecksheetNg::where('daily_checksheet_id', $problemChecksheetData->id)
                                ->where('quantity', '>', 0)
                                ->orderByDesc('quantity')
                                ->first();

                            if ($topProblem) {
                                $topProblemName = NgType::find($topProblem->ng_type_id)->name;
                                $topProblemQuantity = $topProblem->quantity;
                            }
                        }
                    } // End of total_ng > 0 check

                    // No need to fetch part again as it's already fetched above
                }

                // Date formatting code remains the same
                $date = \Carbon\Carbon::parse($production_date);
                $dayName = $this->days[$date->format('l')];
                $monthName = $this->months[$date->format('F')];
                $formattedDate = "$dayName, " . $date->format('d ') . $monthName . $date->format(' Y');

                $data[] = [
                    'supplier_id' => $supplier->id,
                    'supplier_name' => $supplier->name,
                    'total_produced' => $checksheetData->total_produced ?? 0,
                    'total_ok' => $checksheetData->total_ok ?? 0,
                    'total_ng' => $checksheetData->total_ng ?? 0,
                    'part_name' => $part->part_name ?? '-',
                    'part_number' => $part->part_number ?? '-',
                    'judgement' => $checksheetData && $checksheetData->total_produced > 0
                        ? ($checksheetData->total_ng / $checksheetData->total_produced) * 100 >= 5
                            ? 'NG'
                            : 'Good'
                        : '-',
                    'problem' => $topProblemName ?? '-', // Use this instead of top_problem_name for consistency
                    'problem_quantity' => $topProblemQuantity ?? 0, // Add the quantity as a separate field
                    // 'top_problem' => $topProblem ?? '-', // Remove this line to avoid sending the entire object
                    'ng_ratio' => $checksheetData && $checksheetData->total_produced > 0
                        ? number_format(($checksheetData->total_ng / $checksheetData->total_produced) * 100, 0) . '%'
                        : '0%',
                    'ok_ratio' => $checksheetData && $checksheetData->total_produced > 0
                        ? number_format(($checksheetData->total_ok / $checksheetData->total_produced) * 100, 0) . '%'
                        : '0%',
                    'production_date' => $production_date ?? '-',
                    'formatted_date' => $formattedDate ?? '-',
                ];

                $topProblemName = '-';
                $topProblemQuantity = 0;

            }

        }

        return $data;
        // query top part
        // $topPartIds = $data->pluck('top_part_id')->filter()->unique();
        // $parts = Part::whereIn('id', $topPartIds)->get()->keyBy('id');
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
                    'total_produced' => $group->sum('total_produced'),
                    'total_ng' => $group->sum('total_ng'),
                    'total_ok' => $group->sum('total_ok'),
                ];
            });

        // Inisialisasi array untuk menyimpan hasil
        $result = [];

        foreach ($details as $detail) {
            // foreach ($ngTypes as $ngType) {
            //     // Cari ngType yang sesuai dalam relasi ngTypes dan ambil quantity jika ada
            //     $ngTypeData = $detail->ngTypes->firstWhere('name', $ngType);
            //     $ngTypesData[$ngType] = $ngTypeData ? $ngTypeData->pivot->quantity : 0;
            // }

            $ngRatio = $detail['total_produced'] > 0 ? number_format(($detail['total_ng'] / $detail['total_produced']) * 100, 0) . '%' : '0%';
            $okeRatio = $detail['total_produced'] > 0 ? number_format(($detail['total_ok'] / $detail['total_produced']) * 100, 0) . '%' : '0%';
            $ngRatioNumber = $detail['total_produced'] > 0 ? ($detail['total_ng'] / $detail['total_produced']) * 100 : 0;
            $okeRatioNumber = $detail['total_produced'] > 0 ? ($detail['total_ok'] / $detail['total_produced']) * 100 : 0;
            $result[] = [
                'id' => $detail['id'],
                'part_id' => $detail['part_id'],
                'part_name' => $detail['part_name'] ?? '-',
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

    public function getListPart()
    {
        $parts = Part::where('supplier_id', auth()->user()->supplier_id)->get();

        return $parts;
    }

    public function getDataByDay($day)
    {
        // Mengambil data dari DailyChecksheet berdasarkan production_date
        $data = DailyChecksheet::with(['part', 'ngTypes'])
            ->whereDate('production_date', $day)
            ->where('supplier_id', auth()->user()->supplier_id)
            ->get();

        // Inisialisasi array untuk menyimpan hasil
        $result = [];

        // Mengambil semua nama NgType
        $ngTypes = NgType::pluck('name')->toArray();

        foreach ($data as $item) {
            $ngTypesData = [];
            foreach ($ngTypes as $ngType) {
                // Cari ngType yang sesuai dalam relasi ngTypes dan ambil quantity jika ada
                $ngTypeData = $item->ngTypes->firstWhere('name', $ngType);
                $ngTypesData[$ngType] = $ngTypeData ? $ngTypeData->pivot->quantity : 0;
            }

            $result[] = [
                'id' => $item->id,
                'supplier_id' => $item->supplier_id,
                'part_id' => $item->part_id,
                'total_produced' => $item->total_produced,
                'ng' => $item->total_ng ?? null,
                'good' => $item->total_ok ?? null,
                'ng_types' => $ngTypesData,
            ];
        }

        return response()->json($result);
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
        try {
            foreach ($data as $item) {
                if($item['total_produced'] != 0) {
                    $dailyChecksheet = DailyChecksheet::create([
                        'part_id' => $item['part_id'],
                        'supplier_id' => $item['supplier_id'],
                        'production_date' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                        'total_produced' => $item['total_produced'] ?? 0,
                        'total_ng' => $item['ng'] ?? 0,
                        'total_ok' => $item['good'] ?? 0,
                        'created_at' => now()->setTimezone('Asia/Jakarta'),
                        'updated_at' => now()->setTimezone('Asia/Jakarta'),
                    ]);

                    foreach ($item['ng_types'] as $ngType) {
                        $dailyChecksheetNg = DailyChecksheetNg::create([
                            'daily_checksheet_id' => $dailyChecksheet['id'],
                            'ng_type_id' => $ngType['id'],
                            'quantity' => $ngType['quantity']
                        ]);
                    }
                }
            }

            $checksheetData = DailyChecksheet::where('supplier_id', $supplier_id)
                    ->where('production_date', now()->setTimezone('Asia/Jakarta')->format('Y-m-d'))
                    ->select(
                        'part_id',
                        DB::raw('SUM(total_produced) as total_produced'),
                        DB::raw('SUM(total_ng) as total_ng'),
                        DB::raw('SUM(total_ok) as total_ok')
                    )
                    ->groupBy('part_id')
                    ->orderByDesc('total_ng')
                    ->first();

            if($checksheetData) {
                $topProblemName = '-';
                $topProblemQuantity = 0;
                if ($checksheetData->total_ng > 0) {
                    // Get the checksheet with the highest NG count for this specific supplier
                    $problemChecksheetData = DailyChecksheet::where('supplier_id', $supplier_id)
                        ->where('production_date', now()->setTimezone('Asia/Jakarta')->format('Y-m-d'))
                        ->where('total_ng', '>', 0)
                        ->select('id', 'part_id')
                        ->orderByDesc('total_ng')
                        ->first();

                    if ($problemChecksheetData) {
                        $topProblem = DailyChecksheetNg::where('daily_checksheet_id', $problemChecksheetData->id)
                            ->where('quantity', '>', 0)
                            ->orderByDesc('quantity')
                            ->first();

                        if ($topProblem) {
                            $topProblemName = NgType::find($topProblem->ng_type_id)->name;
                            $topProblemQuantity = $topProblem->quantity;
                        }
                    }
                }
                $part = $checksheetData && $checksheetData->part_id? Part::find($checksheetData->part_id) : null;
                $bnf = ($checksheetData->total_ng / $checksheetData->total_produced) * 100 >= 10
                    ? true
                    :false;

                if($bnf) {
                    $latestBnf = Bnf::where('part_id', $checksheetData->part_id)
                        ->where('supplier_id', $supplier_id)
                        ->whereDate('issuance_date', now()->setTimezone('Asia/Jakarta')->format('Y-m-d'))
                        ->where('completion_date', null)
                        ->first();
                    if (!$latestBnf) {
                        $bnfData = [
                            'part_id' => $checksheetData->part_id,
                            'supplier_id' => $supplier_id,
                            'problem' => $topProblemName,
                            'qty' => $checksheetData->total_ng,
                            'issuance_date' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                            'created_by' => auth()->user()->id,
                            // 'description' => $part->part_number . '-' . $part->part_name . ' terdapat NG ' . ($topProblemName ?? 'Unknown Problem') . ' sebanyak ' . $checksheetData->total_ng
                            'description' => 'Generate otomatis dari input daily checksheet'
                        ];

                        // Use BnfService to create the BNF record
                        $bnfService = app(BnfService::class);
                        $bnfResult = $bnfService->create($bnfData);
                    }

                    $latestProblem = ProblemList::where('part_id', $checksheetData->part_id)
                        ->where('supplier_id', $supplier_id)
                        ->whereDate('production_date', now()->setTimezone('Asia/Jakarta')->format('Y-m-d'))
                        ->where('status', 'open')
                        ->first();
                    if (!$latestProblem) {
                        $problemData = [
                            'part_id' => $checksheetData->part_id,
                            'supplier_id' => $supplier_id,
                            'problem_description' => $topProblemName . ' (Generate otomatis dari input dailychecksheet)',
                            'quantity_affected' => $checksheetData->total_ng,
                            'production_date' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                            'finding_location' => 'Production Line', // Default value
                            'status' => 'open',
                            'created_by' => auth()->user()->id,
                        ];

                        $problemService = app(ProblemListService::class);
                        $bnfResult = $problemService->create($problemData);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Daily Checksheet berhasil ditambahkan',
                'checksheet' => $checksheetData,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menambahkan Daily Checksheet: ' . $e->getMessage()
            ];
        }
    }

    private function generatePartCode()
    {
        $latest = Part::latest()->first();
        $sequence = $latest ? intval(substr($latest->code, 3)) + 1 : 1;
        return 'PRT' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function update($data, $id)
    {
        DB::beginTransaction();

        try {
            $part = Part::findOrFail($id);

            $part->update([
                'supplier_id' => $data['supplier_id'],
                'part_number' => $data['part_number'],
                'part_name' => $data['part_name']
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Part berhasil diperbarui',
                'data' => $part
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal memperbarui part: ' . $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $part = Part::find($id);

            if ($part) {
                // Hapus relasi checksheet dan problem list
                $part->checksheets()->delete();
                $part->problems()->delete();

                $part->delete();

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Data berhasil dihapus.',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ];
        }
    }


    
    


}
