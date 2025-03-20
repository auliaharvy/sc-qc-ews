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
                $actionBtn = '<a href="' . route('daily-check-sheet.detail', ['supplier_id' => $row->supplier_id, 'production_date' => $row->production_date]) . '"
                            class="edit btn btn-warning btn-sm me-2"><i class="fa fa-eye"></i></a>';
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
            // $data = DailyChecksheet::with(['supplier'])
            // ->select([
            //     'supplier_id',
            //     'production_date',
            //     DB::raw('SUM(total_produced) as total_produced'),
            //     DB::raw('SUM(total_ng) as total_ng'),
            //     DB::raw('SUM(total_ok) as total_ok'),
            //     DB::raw('DATE(production_date) as formatted_date'), // Menambahkan formatted_date
            //     DB::raw('(SELECT part_id
            //           FROM daily_checksheet d2
            //           WHERE d2.supplier_id = daily_checksheet.supplier_id
            //             AND d2.production_date = daily_checksheet.production_date
            //           GROUP BY d2.part_id
            //           ORDER BY SUM(d2.total_ng) DESC
            //           LIMIT 1) as top_part_id'),
            //     // DB::raw('(SELECT ng_types.name
            //     //       FROM daily_check_sheet_ng_types
            //     //       JOIN ng_types ON daily_check_sheet_ng_types.ng_types_id = ng_types.id
            //     //       WHERE daily_check_sheet_ng_types.daily_check_sheet_id = daily_checksheet.id
            //     //       GROUP BY ng_types.name
            //     //       ORDER BY SUM(daily_check_sheet_ng_types.quantity) DESC
            //     //       LIMIT 1) as top_ng_type_name')
            // ])
            // ->groupBy('supplier_id', 'formatted_date', 'production_date') // Menggunakan formatted_date untuk groupBy
            // ->where('production_date', $production_date)
            // ->get();

            $data = Supplier::select([
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                DB::raw("COALESCE(SUM(daily_checksheet.total_produced), 0) as total_produced"),
                DB::raw("COALESCE(SUM(daily_checksheet.total_ng), 0) as total_ng"),
                DB::raw("COALESCE(SUM(daily_checksheet.total_ok), 0) as total_ok"),
                DB::raw("DATE('$production_date') as production_date"), // Gunakan parameter tanggal
                DB::raw('(SELECT part_id
                      FROM daily_checksheet d2
                      WHERE d2.supplier_id = suppliers.id
                        AND d2.production_date = "' . $production_date . '"
                      GROUP BY d2.part_id
                      ORDER BY SUM(d2.total_ng) DESC
                      LIMIT 1) as top_part_id')
            ])
            ->leftJoin('daily_checksheet', function($join) use ($production_date) {
                $join->on('suppliers.id', '=', 'daily_checksheet.supplier_id')
                    ->whereDate('daily_checksheet.production_date', $production_date);
            })
            ->groupBy('suppliers.id', 'suppliers.name')
            ->get();
        }

        // query top part
        $topPartIds = $data->pluck('top_part_id')->filter()->unique();
        $parts = Part::whereIn('id', $topPartIds)->get()->keyBy('id');


        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('part_number', function($row) use ($parts) {
                return $parts[$row->top_part_id]->part_number?? '-';
            })
            ->addColumn('part_name', function($row) use ($parts) {
                $totalProduced = $row->total_produced;
                $totalNg = $row->total_ng;
                if ($totalProduced > 0) {
                    if (number_format(($totalNg / $totalProduced) * 100, 0) > 5) {
                        return $parts[$row->top_part_id]->part_name;
                    } else {
                        return '-';
                    }
                } else {
                    return '-';
                }
            })
            ->addColumn('supplier_name', function($row) {
                return $row->supplier_name ?? '-';
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
            ->addColumn('judgement', function($row) {
                $totalProduced = $row->total_produced;
                $totalNg = $row->total_ng;
                if ($totalProduced > 0) {
                    if (number_format(($totalNg / $totalProduced) * 100, 0) >= 5) {
                        return 'NG';
                    } else {
                        return 'Good';
                    }
                } else {
                    return '-';
                }
            })
            ->addColumn('ng_ratio', function($row) {
                $totalProduced = $row->total_produced;
                $totalNg = $row->total_ng;
                if ($totalProduced > 0) {
                    return number_format(($totalNg / $totalProduced) * 100, 0) . '%';
                } else {
                    return '0%';
                }
            })
            ->addColumn('oke_ratio', function($row) {
                $totalProduced = $row->total_produced;
                $totalOk = $row->total_ok;
                if ($totalProduced > 0) {
                    return number_format(($totalOk / $totalProduced) * 100, 0) . '%';
                } else {
                    return '0%';
                }
            })
            ->addColumn('action', function ($row) {
                $actionBtn = '<a href="' . route('daily-check-sheet.detail', ['supplier_id' => $row->supplier_id, 'production_date' => $row->production_date]) . '"
                            class="edit btn btn-warning btn-sm me-2"><i class="fa fa-eye"></i></a>';
                return '<div class="d-flex">' . $actionBtn . '</div>';
            })
            ->rawColumns(['action', 'ng_ratio', 'oke_ratio', 'part_name', 'part_number'])
            ->make(true);
    }

    // public function historyBySupplierAndDate($supplier_id, $production_date)
    // {
    //     // Mengambil detail dari DailyChecksheet berdasarkan supplier_id dan production_date
    //     $details = DailyChecksheet::with(['supplier', 'part', 'ngTypes'])
    //         ->where('supplier_id', $supplier_id)
    //         ->whereDate('production_date', $production_date)
    //         ->get();

    //     // Mengambil semua nama NgType
    //     $ngTypes = NgType::pluck('name')->toArray();

    //     // Inisialisasi DataTables
    //     $dataTables = DataTables::of($details)
    //         ->addIndexColumn()
    //         ->addColumn('supplier_name', function($row) {
    //             return $row->supplier->name ?? '-';
    //         })
    //         ->addColumn('part_number', function($row) {
    //             return $row->part->part_number ?? '-';
    //         })
    //         ->addColumn('part_name', function($row) {
    //             return $row->part->part_name ?? '-';
    //         })
    //         ->addColumn('formated_date', function($row) {
    //             if (!$row->production_date) {
    //                 return '-';
    //             }

    //             $date = \Carbon\Carbon::parse($row->production_date);
    //             $dayName = $this->days[$date->format('l')];
    //             $monthName = $this->months[$date->format('F')];

    //             return "$dayName, " . $date->format('d ') . $monthName . $date->format(' Y');
    //         })
    //         ->addColumn('jam_buat', function($row) {
    //             if (!$row->created_at) {
    //                 return '-';
    //             }
    //             return \Carbon\Carbon::parse($row->created_at)->format('H:i'); // Format: Jam:Menit
    //         })
    //         ->addColumn('ng_ratio', function($row) {
    //             $totalProduced = $row->total_produced;
    //             $totalNg = $row->total_ng;
    //             if ($totalProduced > 0) {
    //                 return number_format(($totalNg / $totalProduced) * 100, 2) . '%';
    //             } else {
    //                 return '0%';
    //             }
    //         })
    //         ->addColumn('oke_ratio', function($row) {
    //             $totalProduced = $row->total_produced;
    //             $totalOk = $row->total_ok;
    //             if ($totalProduced > 0) {
    //                 return number_format(($totalOk / $totalProduced) * 100, 2) . '%';
    //             } else {
    //                 return '0%';
    //             }
    //         });

    //     // Menambahkan kolom untuk setiap NgType
    //     foreach ($ngTypes as $ngType) {
    //         $dataTables->addColumn($ngType, function($row) use ($ngType) {
    //             // Cari ngType yang sesuai dalam relasi ngTypes
    //             $ngTypeData = $row->ngTypes->firstWhere('name', $ngType);
    //             return $ngTypeData ? $ngTypeData->pivot->quantity : 0;
    //         });
    //     }

    //     // Mengembalikan respons JSON dari DataTables
    //     return $dataTables->rawColumns(['ng_ratio', 'oke_ratio'])->with('ngTypes', $ngTypes)->make(true);
    // }

    public function getDetailBySupplierAndDate($supplier_id, $production_date)
    {
        // Mengambil detail dari DailyChecksheet berdasarkan supplier_id dan production_date
        $details = DailyChecksheet::where('supplier_id', $supplier_id)
            ->whereDate('production_date', $production_date)
            ->get()
            ->groupBy('part_id')
            ->map(function ($group) {
                return [
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

            DB::commit();

            return [
                'success' => true,
                'message' => 'Daily Checksheet berhasil ditambahkan'
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
