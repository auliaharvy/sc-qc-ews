<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DailyChecksheetService;
use Illuminate\Support\Facades\Storage;
use App\Models\NgType;
use App\Models\Part;
use App\Models\Supplier;
use App\Models\Bnf;
use App\Models\ProblemList;
use App\Models\DailyChecksheet;
use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
    protected $dailyChecksheetService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DailyChecksheetService $dailyChecksheetService)
    {
        $this->middleware('auth');
        $this->dailyChecksheetService = $dailyChecksheetService;
    }

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
    private function formatDate($date)
    {
        if (!$date) return '-';
        $date = \Carbon\Carbon::parse($date);
        $dayName = $this->days[$date->format('l')];
        $monthName = $this->months[$date->format('F')];
        return "$dayName, " . $date->format('d ') . $monthName . $date->format(' Y');
    }

    private function formatDateTime($date)
    {
        $date = \Carbon\Carbon::parse($date);
        $dayName = $this->days[$date->format('l')];
        $monthName = $this->months[$date->format('F')];
        return "$dayName, " . $date->format('d ') . $monthName . $date->format(' Y H:i');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $today = now()->setTimezone('Asia/Jakarta')->format('Y-m-d');
        $selectedDate = request('filter_date', $today);
        $userRole = auth()->user()->roles()->first()->name;
        $supplierId = auth()->user()->supplier_id;
        $supplier = Supplier::where('id', $supplierId)->first();

        // query data statistik hari ini
        $todayStatsQuery = DailyChecksheet::query()
        ->select([
            DB::raw('SUM(total_produced) as total'),
            DB::raw('SUM(total_ok) as ok'),
            DB::raw('SUM(total_ng) as ng')
        ]);

        if ($userRole == 'Admin Supplier') {
            // Get production data for current month
            $startOfMonth = now()->setTimezone('Asia/Jakarta')->startOfMonth();
            $endOfMonth = now()->setTimezone('Asia/Jakarta')->endOfMonth();

            // query data statistik hari ini
            $todayStatsQuery->where('supplier_id', $supplierId);
            $todayStats = $todayStatsQuery
            ->where('production_date',$today)
            ->first();

            // query data chart
            $dailyData = DailyCheckSheet::where('supplier_id', $supplierId)->whereBetween('production_date', [$startOfMonth, $endOfMonth])
                ->get()
                ->groupBy(function($date) {
                    return \Carbon\Carbon::parse($date->production_date)->format('d');
                });

            // Prepare chart data
            $dates = [];
            $okData = [];
            $ngData = [];

            for ($day = 1; $day <= $endOfMonth->day; $day++) {
                $dates[] = $day;
                $ok = 0;
                $ng = 0;

                if ($dailyData->has($day)) {
                    foreach ($dailyData[$day] as $data) {
                        $ok += $data->total_ok;
                        $ng += $data->total_ng;
                    }
                }

                $okData[] = $ok;
                $ngData[] = $ng;
            }

            $parts = $this->dailyChecksheetService->getListPart();
            $data = $this->dailyChecksheetService->getDetailBySupplierAndDate($supplierId, $today);
            // return $data;
            $ngTypes = NgType::pluck('name')->toArray();
            $title = 'Tambah Daily Checksheet';
            return view('home-supplier', compact('title', 'parts', 'ngTypes', 'data', 'dates', 'okData', 'ngData', 'supplier', 'todayStats'));
        } else {
            $jsonData = Storage::get('data/dummy-data.json');
            $data = json_decode($jsonData, true);

            // query data chart
            $startOfMonth = now()->setTimezone('Asia/Jakarta')->startOfMonth();
            $endOfMonth = now()->setTimezone('Asia/Jakarta')->endOfMonth();

            $dailyData = DailyCheckSheet::whereBetween('production_date', [$startOfMonth, $endOfMonth])
                ->get()
                ->groupBy(function($date) {
                    return \Carbon\Carbon::parse($date->production_date)->format('d');
                });

            // Prepare chart data
            $dates = [];
            $okData = [];
            $ngData = [];

            for ($day = 1; $day <= $endOfMonth->day; $day++) {
                $dates[] = $day;
                $ok = 0;
                $ng = 0;

                if ($dailyData->has($day)) {
                    foreach ($dailyData[$day] as $data) {
                        $ok += $data->total_ok;
                        $ng += $data->total_ng;
                    }
                }

                $okData[] = $ok;
                $ngData[] = $ng;
            }

            // query data statistik hari ini
            $todayStats = $todayStatsQuery
            ->where('production_date', $today)
            ->first();

            // query list data early warning tabel
            $dataQC = $this->dailyChecksheetService->dataDashboardQuality($selectedDate);
            $tableQuality = $dataQC->getData()->data;
            // query list data BNF tabel
            $tableBnf = Bnf::where('status', 'open')
                ->with(['part', 'supplier'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($bnf) {
                    return [
                        'id' => $bnf->id,
                        'supplier_name' => $bnf->supplier->name,
                        'part_name' => $bnf->part->part_name,
                        'problem' => $bnf->problem,
                        'qty' => $bnf->qty,
                        'description' => $bnf->description,
                        'issuance_date' => $bnf->issuance_date,
                        'status' => $bnf->status,
                    ];
                });

            // query list data Problem List tabel
            $tableProblem = ProblemList::where('status', 'open')
                ->with(['part', 'supplier'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($problem) {
                    $statusBadge = $problem->status == 'open' ?
                        '<span class="badge bg-danger text-white">Belum Selesai</span>' :
                        '<span class="badge bg-success">Selesai</span>';

                    $carButton = empty($problem->car_file) ?
                        '<button type="button" class="btn btn-primary btn-sm upload-car" data-id="' . $problem->id . '">
                            <i class="ti-upload"></i> Upload CAR
                        </button>' :
                        '<a href="' . asset('storage/' . $problem->car_file) . '" download>
                          '. $problem->no_car .'
                        </a>';

                    $a3Report = empty($problem->a3_report) ?
                        empty($problem->car_file) ? '-' :
                        '-' :
                        '<a href="' . asset('storage/' . $problem->a3_report) . '" class="btn btn-success btn-sm" download>
                            <i class="ti-download"></i> Download A3 Report
                        </a>';

                    $actionButtons = '';
                    if ($problem->status == 'open') {
                        if (auth()->user()->id == $problem->created_by) {
                            $actionButtons .= '<a href="' . route('parts.edit', $problem->id) . '"
                                    class="edit btn btn-warning btn-sm me-2"><i class="ti-pencil-alt"></i></a>';
                        }
                        if (auth()->user()->roles()->first()->name == 'admin') {
                            $actionButtons .= '<button type="button" name="close" data-id="' . $problem->id . '" class="closeBnf btn btn-primary btn-sm">Selesaikan</button>';
                        }
                    }

                    return [
                        'id' => $problem->id,
                        'supplier_name' => $problem->supplier->name ?? '-',
                        'part_name' => $problem->part->part_name ?? '-',
                        'part_number' => $problem->part->part_number ?? '-',
                        'problem_description' => $problem->problem_description,
                        'qty' => $problem->quantity_affected,
                        'status' => $statusBadge,
                        'car' => $carButton,
                        'a3_report' => $a3Report,
                        'action' => '<div class="d-flex">' . $actionButtons . '</div>',
                        'formated_date' => $this->formatDate($problem->production_date),
                        'created_at' => $this->formatDateTime($problem->created_at)
                    ];
                })->toArray();

            return view('home', [
                'performance' => $data['performance'],
                'qualityWarnings' => $data['quality_warnings'],
                'badNews' => $data['bad_news'],
                'problems' => $data['problems'],
                'tableQuality' => $tableQuality,
                'tableBnf' => $tableBnf,
                'tableProblem' => $tableProblem,
                'dates' => $dates,
                'okData' => $okData,
                'ngData' => $ngData,
                'todayStats' => $todayStats
            ]);
        }
    }

    public function indexMonitoring(Request $request)
    {
        $today = now()->setTimezone('Asia/Jakarta')->format('Y-m-d');
        $selectedDate = request('filter_date', $today);
        $userRole = auth()->user()->roles()->first()->name;
        $supplierId = auth()->user()->supplier_id;
        $supplier = Supplier::where('id', $supplierId)->first();

        // query data statistik hari ini
        $todayStatsQuery = DailyChecksheet::query()
        ->select([
            DB::raw('SUM(total_produced) as total'),
            DB::raw('SUM(total_ok) as ok'),
            DB::raw('SUM(total_ng) as ng')
        ]);

        if ($userRole == 'Admin Supplier') {
            // Get production data for current month
            $startOfMonth = now()->setTimezone('Asia/Jakarta')->startOfMonth();
            $endOfMonth = now()->setTimezone('Asia/Jakarta')->endOfMonth();

            // query data statistik hari ini
            $todayStatsQuery->where('supplier_id', $supplierId);
            $todayStats = $todayStatsQuery
            ->where('production_date',$today)
            ->first();

            // query data chart
            $dailyData = DailyCheckSheet::where('supplier_id', $supplierId)->whereBetween('production_date', [$startOfMonth, $endOfMonth])
                ->get()
                ->groupBy(function($date) {
                    return \Carbon\Carbon::parse($date->production_date)->format('d');
                });

            // Prepare chart data
            $dates = [];
            $okData = [];
            $ngData = [];

            for ($day = 1; $day <= $endOfMonth->day; $day++) {
                $dates[] = $day;
                $ok = 0;
                $ng = 0;

                if ($dailyData->has($day)) {
                    foreach ($dailyData[$day] as $data) {
                        $ok += $data->total_ok;
                        $ng += $data->total_ng;
                    }
                }

                $okData[] = $ok;
                $ngData[] = $ng;
            }

            $parts = $this->dailyChecksheetService->getListPart();
            $data = $this->dailyChecksheetService->getDetailBySupplierAndDate($supplierId, $today);
            // return $data;
            $ngTypes = NgType::pluck('name')->toArray();
            $title = 'Tambah Daily Checksheet';
            return view('home-supplier', compact('title', 'parts', 'ngTypes', 'data', 'dates', 'okData', 'ngData', 'supplier', 'todayStats'));
        } else {
            $jsonData = Storage::get('data/dummy-data.json');
            $data = json_decode($jsonData, true);

            // query data chart
            $startOfMonth = now()->setTimezone('Asia/Jakarta')->startOfMonth();
            $endOfMonth = now()->setTimezone('Asia/Jakarta')->endOfMonth();

            $dailyData = DailyCheckSheet::whereBetween('production_date', [$startOfMonth, $endOfMonth])
                ->get()
                ->groupBy(function($date) {
                    return \Carbon\Carbon::parse($date->production_date)->format('d');
                });

            // Prepare chart data
            $dates = [];
            $okData = [];
            $ngData = [];

            for ($day = 1; $day <= $endOfMonth->day; $day++) {
                $dates[] = $day;
                $ok = 0;
                $ng = 0;

                if ($dailyData->has($day)) {
                    foreach ($dailyData[$day] as $data) {
                        $ok += $data->total_ok;
                        $ng += $data->total_ng;
                    }
                }

                $okData[] = $ok;
                $ngData[] = $ng;
            }

            // query data statistik hari ini
            $todayStats = $todayStatsQuery
            ->where('production_date', $today)
            ->first();

            // query list data early warning tabel
            $dataQC = $this->dailyChecksheetService->dataDashboardQuality($selectedDate);
            $tableQuality = $dataQC->getData()->data;
            // return $tableQuality;
            // var_dump($data);
            return view('home-monitoring', [
                'performance' => $data['performance'],
                'qualityWarnings' => $data['quality_warnings'],
                'badNews' => $data['bad_news'],
                'problems' => $data['problems'],
                'tableQuality' => $tableQuality,
                'dates' => $dates,
                'okData' => $okData,
                'ngData' => $ngData,
                'todayStats' => $todayStats
            ]);
        }
    }
}
