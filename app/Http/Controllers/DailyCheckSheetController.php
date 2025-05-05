<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DailyChecksheetService;
use Illuminate\Support\Facades\Storage;
use App\Models\NgType;
use App\Models\Supplier;

class DailyCheckSheetController extends Controller
{
    protected $dailyChecksheetService;

    public function __construct(DailyChecksheetService $dailyChecksheetService)
    {
        $this->middleware('can:read inspeksi');
        $this->dailyChecksheetService = $dailyChecksheetService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $title = 'Daily Checksheet';
        if ($request->ajax()) {
            return $this->dailyChecksheetService->datatable();
        }
        // var_dump($data);
        return view('checksheets.index', compact('title'));
    }


    public function detail(Request $request, $supplier_id, $production_date)
    {
        // Logika untuk mengambil detail berdasarkan supplier_id dan production_date
        $title = 'Daily Checksheet Detail';
        $ngTypes = NgType::pluck('name')->toArray();
        $supplierName = Supplier::where('id', $supplier_id)->first()->name;
        $dailyCheckSheetData = $this->dailyChecksheetService->getDetailBySupplierAndDate($supplier_id, $production_date);
        // return $dailyCheckSheetData;
        if ($request->ajax()) {
            return $this->dailyChecksheetService->getDetailBySupplierAndDate($supplier_id, $production_date);
        }
        // var_dump($data);
        return view('checksheets.detail', compact('title', 'supplier_id', 'production_date', 'ngTypes', 'dailyCheckSheetData', 'supplierName'));
    }

    public function create()
    {
        $parts = $this->dailyChecksheetService->getListPart();
        $data = $this->dailyChecksheetService->getDataByDay(now()->format('Y-m-d'));
        $ngTypes = NgType::pluck('name')->toArray();
        $title = 'Tambah Daily Checksheet';
        return view('checksheets.create', compact('title', 'parts', 'ngTypes', 'data'));
    }

    public function store(Request $request)
    {
        // return $request;
        $data = [];
        $totalNg = 0;

        // mendapatkan data shift
        $currentTime = now()->setTimezone('Asia/Jakarta')->format('H:i');
        $shift = '';

        if ($currentTime >= '09:00' && $currentTime < '21:00') {
            $shift = 'day';
        } else {
            $shift = 'night';
        }

        // Mengambil semua part_id dari request
        $partIds = $request->input('part_id');
        $ngTypes = NgType::get();
        foreach ($partIds as $partId) {
            $ngData = [];
            foreach ($ngTypes as $ngType) {
                $ngTypeName = str_replace(' ', '_', $ngType->name);
                // Retrieve the quantity from the request input
                $quantity = $request->input("ngtype-{$ngTypeName}.{$partId}", 0);

                // Create an NG data object with the required properties
                $ngData[] = [
                    'id' => $ngType->id,
                    'name' => $ngType->name,
                    'quantity' => $quantity
                ];

                // Add the quantity to the total NG count
                // $totalNg += $quantity;
            }

            $totalNg = $request->input("ng.$partId");
            $totalOk = $request->input("ok.$partId");
            $totalProduced = $request->input("total_produced.$partId");
            if ($totalNg != 0 || $totalOk != 0) {
                if ($totalProduced == 0 || $totalProduced == null) {
                    $message = 'Hasil Produksi tidak boleh 0';
                    return back()->withInput()->with('error', $message);
                }
            }
            $data[] = [
                'supplier_id' => $request->input("supplier_id.$partId"),
                'part_id' => $partId,
                'total_produced' => $request->input("total_produced.$partId"),
                'ng' => $request->input("ng.$partId"),
                'good' => $request->input("ok.$partId"),
                'ng_types' => $ngData,
                'shift' => $shift,
            ];

            // $bnf = $this->bnfService->create($request->all());
        }
        // return $data;
        $result = $this->dailyChecksheetService->create($data);
        // return $result;
        if ($result['success']) {
            return redirect()->route('daily-check-sheet')->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    // TODO: buat halaman dan fungsi tambah dan halaman tambah
}
