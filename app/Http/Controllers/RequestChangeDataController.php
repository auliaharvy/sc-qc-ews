<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DailyChecksheetService;
use App\Services\RequestChangeDataService;
use Illuminate\Support\Facades\Storage;
use App\Models\NgType;
use App\Models\Supplier;
use App\Models\DailyChecksheetNg;
use App\Models\RequestChangeDataDetail;



class RequestChangeDataController extends Controller
{
    protected $requestChangeDataService;

    public function __construct(RequestChangeDataService $requestChangeDataService)
    {
        $this->middleware('can:read inspeksi');
        $this->requestChangeDataService = $requestChangeDataService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $title = 'Request Change Daily Checksheet';
        if ($request->ajax()) {
            return $this->requestChangeDataService->dataTable();
        }
        // var_dump($data);
        return view('request-change-data.index', compact('title'));
    }


    public function create(Request $request, $supplier_id, $production_date)
    {
        // Logika untuk mengambil detail berdasarkan supplier_id dan production_date
        $title = 'Request Change Daily Checksheet';
        $ngTypes = NgType::get();
        $supplierName = Supplier::where('id', $supplier_id)->first()->name;
        $parts = $this->requestChangeDataService->getListPart($supplier_id);
        // Ensure $checklist_id is always an array for whereIn, or use where if single value
        $dailyCheckSheetData = $this->requestChangeDataService->getDetailBySupplierAndDate($supplier_id, $production_date);
        $dailyCheckSheetData = collect($dailyCheckSheetData)->keyBy('part_id')->toArray();
        $checksheetIds = [];
        foreach ($dailyCheckSheetData as $detail) {
            $checksheetIds[] = $detail['id'];
        }
        $dailyNgTypesRaw = DailyChecksheetNg::join('daily_checksheet', 'daily_checksheet_ng.daily_checksheet_id', '=', 'daily_checksheet.id')
            ->whereIn('daily_checksheet_ng.daily_checksheet_id', $checksheetIds)
            ->select([
                'daily_checksheet.id',
                'daily_checksheet.production_date',
                'daily_checksheet.part_id',
                'daily_checksheet_ng.ng_type_id',
                'daily_checksheet_ng.quantity'
            ])
            ->get()
            ->toArray();

        $dailyNgTypes = [];
        foreach ($dailyNgTypesRaw as $item) {
            $key = $item['part_id'] . '_' . $item['ng_type_id'];
            $dailyNgTypes[$key] = $item['quantity'];
        }

        // return $dailyCheckSheetData;
        if ($request->ajax()) {
            return $this->requestChangeDataService->getDetailBySupplierAndDate($supplier_id, $production_date);
        }
        return view('request-change-data.create', compact('title', 'supplier_id', 'production_date', 'ngTypes', 'dailyCheckSheetData', 'supplierName', 'parts', 'dailyNgTypes'));
    }

    public function detail(Request $request, $supplier_id, $production_date)
    {
         // Logika untuk mengambil detail berdasarkan supplier_id dan production_date
         $title = 'Request Change Daily Checksheet Detail';
         $ngTypes = NgType::get();
         $parts = $this->requestChangeDataService->getListPart($supplier_id);


         // data sebelumnya
         $dailyCheckSheetData = $this->requestChangeDataService->getDetailBySupplierAndDate($supplier_id, $production_date);
        $dailyCheckSheetData = collect($dailyCheckSheetData)->keyBy('part_id')->toArray();
        $checksheetIds = [];
        foreach ($dailyCheckSheetData as $detail) {
            $checksheetIds[] = $detail['id'];
        }
        $dailyNgTypesRaw = DailyChecksheetNg::join('daily_checksheet', 'daily_checksheet_ng.daily_checksheet_id', '=', 'daily_checksheet.id')
            ->whereIn('daily_checksheet_ng.daily_checksheet_id', $checksheetIds)
            ->select([
                'daily_checksheet.id',
                'daily_checksheet.production_date',
                'daily_checksheet.part_id',
                'daily_checksheet_ng.ng_type_id',
                'daily_checksheet_ng.quantity'
            ])
            ->get()
            ->toArray();

        $dailyNgTypes = [];
        foreach ($dailyNgTypesRaw as $item) {
            $key = $item['part_id'] . '_' . $item['ng_type_id'];
            $dailyNgTypes[$key] = $item['quantity'];
        }


        //data sesudahnya
         $requestChangeData = $this->requestChangeDataService->getDetailRequestChangeBySupplierAndDate($supplier_id, $production_date);
         $requestChangeData = collect($requestChangeData)->keyBy('part_id')->toArray();
         $totalPart = collect($requestChangeData)->sum('total_produced'); 
         $requestChangeIds = [];
         foreach ($requestChangeData as $detail) {
             $requestChangeIds[] = $detail['id'];
         }
         $requestChangeDetailRaws = RequestChangeDataDetail::join('request_change_data', 'request_change_data_detail.request_change_data_id', '=', 'request_change_data.id')
             ->whereIn('request_change_data_detail.request_change_data_id', $requestChangeIds)
             ->select([
                 'request_change_data.id',
                 'request_change_data.production_date',
                 'request_change_data.part_id',
                 'request_change_data_detail.ng_type_id',
                 'request_change_data_detail.quantity'
             ])
             ->get()
             ->toArray();
        
             $requestChangeNgType = [];
            foreach ($requestChangeDetailRaws as $item) {
                $key = $item['part_id'] . '_' . $item['ng_type_id'];
                $requestChangeNgType[$key] = $item['quantity'];
            }


        return view('request-change-data.detail', compact('title', 'supplier_id', 'production_date', 'ngTypes', 'requestChangeData', 'requestChangeNgType', 'parts', 'totalPart', 'dailyCheckSheetData', 'dailyNgTypes'));
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
                $quantity = $request->input("ngtype-{$ngType->id}.{$partId}", 0);
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
            $daily_checksheet_id = $request->input("daily_checksheet_id.$partId");


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
                'daily_checksheet_id' => $daily_checksheet_id,
                'production_date' => $request->input("production_date.$partId"),
            ];

            // $bnf = $this->bnfService->create($request->all());
        }

        // return $data;
        $result = $this->requestChangeDataService->create($data);
        // return $result;
        if ($result['success']) {
            return redirect()->route('request-change-data')->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    public function update(Request $request)
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
                $quantity = $request->input("ngtype_request-{$ngType->id}.{$partId}", 0);
                $ngData[] = [
                    'id' => $ngType->id,
                    'name' => $ngType->name,
                    'quantity' => $quantity
                ];

                // Add the quantity to the total NG count
                // $totalNg += $quantity;
            }

            $totalNg = $request->input("ng_request.$partId");
            $totalOk = $request->input("ok_request.$partId");
            $totalProduced = $request->input("total_produced_request.$partId");
            $daily_checksheet_id = $request->input("daily_checksheet_id_request.$partId");
            $request_change_data_id = $request->input("id_request.$partId");


            if ($totalNg != 0 || $totalOk != 0) {
                if ($totalProduced == 0 || $totalProduced == null) {
                    $message = 'Hasil Produksi tidak boleh 0';
                    return back()->withInput()->with('error', $message);
                }
            }
            $data[] = [
                'supplier_id' => $request->input("supplier_id_request.$partId"),
                'part_id' => $partId,
                'total_produced' => $request->input("total_produced_request.$partId"),
                'ng' => $request->input("ng_request.$partId"),
                'good' => $request->input("ok_request.$partId"),
                'ng_types' => $ngData,
                'shift' => $shift,
                'daily_checksheet_id' => $daily_checksheet_id,
                'production_date' => $request->input("production_date_request.$partId"),
                'request_change_data_id' => $request_change_data_id,
            ];

            // $bnf = $this->bnfService->create($request->all());
        }


        // return $data;
        $result = $this->requestChangeDataService->updateRequestChange($data);

        // return $result;
        if ($result['success']) {
            return redirect()->route('daily-check-sheet')->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    // TODO: buat halaman dan fungsi tambah dan halaman tambah
}



