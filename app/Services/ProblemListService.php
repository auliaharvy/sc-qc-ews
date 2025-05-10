<?php
// TODO: add fungsi upload a3 report
// TODO: add fungsi selesaikan a3 report
namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\BnfNotification;
use App\Notifications\ProblemListNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\Bnf;
use App\Models\ProblemList;
use App\Models\Part;
use App\Models\Supplier;

class ProblemListService
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
        $request = request();

        if ($userRole == 'Admin Supplier') {
            $data = ProblemList::with(['supplier', 'part'])->select('problem_lists.*')->where('supplier_id', $supplierId)
            ->orderBy('status', 'asc')
            ->get();
        } else {
            $data = ProblemList::with(['supplier', 'part'])->select('problem_lists.*')
            ->orderBy('created_at', 'desc')
            ->orderBy('status', 'asc')
            ->get();
        }

        // Apply filter by supplier_id if present
        // if ($request->filled('supplier_id')) {
        //     $data = $data->where('supplier_id', $request->input('supplier_id'));
        // }
        // Apply filter by status if present
        // if ($request->filled('status')) {
        //     $data = $data->where('status', $request->input('status'));
        // }

        // $data = $data->orderBy('created_at', 'desc')->orderBy('status', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('part_name', function($row) {
                return $row->part->part_name ?? '-';
            })
            ->addColumn('part_number', function($row) {
                return $row->part->part_number ?? '-';
            })
            ->addColumn('supplier_name', function($row) {
                return $row->supplier->name ?? '-';
            })
            ->addColumn('status', function($row) {
                return $row->status == 'open' ? '<span class="badge bg-danger text-white">Belum Selesai</span>' : '<span class="badge bg-success">Selesai</span>';
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
            ->addColumn('car', function($row) {
                if (auth()->user()->roles()->first()->name == 'Admin Supplier') {
                    if (empty($row->car_file)) {
                        return '-';
                    } else {
                        return '<a href="' . url($row->car_file) . '" class="btn btn-success btn-sm" download>
                                    <i class="ti-download"></i> Download CAR
                                </a>';
                    }
                } else {
                    if (empty($row->car_file)) {
                        return '<button type="button" class="btn btn-primary btn-sm upload-car" data-id="' . $row->id . '">
                                    <i class="ti-upload"></i> Upload CAR
                                </button>';
                    } else {
                        return '<a href="' . url($row->car_file) . '" class="btn btn-success btn-sm" download>
                                    <i class="ti-download"></i> Download CAR
                                </a>';
                    }
                }

            })

            ->addColumn('a3_report', function($row) {
                if (auth()->user()->roles()->first()->name == 'Admin Supplier') {
                    if (empty($row->car_file)) {
                        return '-';
                    } else {
                        if(empty($row->a3_report)) {
                            return '<button type="button" class="btn btn-primary btn-sm upload-a3-report" data-id="' . $row->id . '">
                                    <i class="ti-upload"></i> Upload A3 Report
                                </button>';
                        } else {
                            return '<a href="'. url($row->a3_report) . '" class="btn btn-success btn-sm" download>
                                        <i class="ti-download"></i> Download A3 Report
                                    </a>';
                        }
                    }
                } else {
                    if (empty($row->a3_report)) {
                        return '-';
                    } else {
                        return '<a href="'. url($row->a3_report) . '" class="btn btn-success btn-sm" download>
                                        <i class="ti-download"></i> Download A3 Report
                                    </a>';
                    }
                }

            })
            ->addColumn('action', function ($row) {
                if ($row->status == 'open') {
                    $actionBtn = '';
                    if (auth()->user()->id == $row->created_by) {
                        if(empty($row->car_file)) {
                            $actionBtn .= '<a href="' . route('parts.edit', $row->id) . '"
                                class="edit btn btn-warning btn-sm me-2"><i class="ti-pencil-alt"></i></a>';
                        }
                    }

                    if (auth()->user()->roles()->first()->name == 'admin') {
                        $actionBtn .= '<button type="button" name="close" data-id="' . $row->id . '" class="closeBnf btn btn-primary btn-sm">Selesaikan</button>';
                    }
                    return '<div class="d-flex">' . $actionBtn . '</div>';
                } else {
                    return '<div class="d-flex"></div>';
                }
            })
            ->editColumn('created_at', function($row) {
                $date = \Carbon\Carbon::parse($row->created_at);
                $dayName = $this->days[$date->format('l')];
                $monthName = $this->months[$date->format('F')];
                return "$dayName, " . $date->format('d ') . $monthName . $date->format(' Y H:i');
            })
            ->rawColumns(['action', 'status', 'car', 'a3_report'])
            ->make(true);
    }

    public function getById($id)
    {
        return Bnf::with(['part', 'supplier'])->findOrFail($id);
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $problem = ProblemList::create([
                'part_id' => $data['part_id'],
                'supplier_id' => $data['supplier_id'],
                'problem_description' => $data['problem_description'],
                'quantity_affected' => $data['quantity_affected'],
                'production_date' => $data['production_date'],
                'finding_location' => $data['finding_location'],
                'status' => 'open',
                'created_by' => auth()->user()->id,
            ]);

            $supplier = Supplier::find($data['supplier_id']);
            $part = Part::find($data['part_id']);

            $adminUsers = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            $loggedInUser = auth()->user();

            $date = \Carbon\Carbon::parse($data['production_date']);
            $dayName = $this->days[$date->format('l')];
            $monthName = $this->months[$date->format('F')];
            $formatted_date = "$dayName, " . $date->format('d ') . $monthName . $date->format(' Y');

            $notificationData = [
                'supplier' => $supplier->name,
                'part_number' => $part->part_number,
                'part_name' => $part->part_name,
                'problem' => $data['problem_description'],
                'qty' => $data['quantity_affected'],
                'created_by' => auth()->user()->id,
                'date' => $formatted_date,
                // 'description' => $data['description']
            ];

            try {
                if ($supplier) {
                    // Notification::send($supplier, new BnfNotification($notificationData));
                    Notification::route('mail', $supplier->email)->notify(new ProblemListNotification($notificationData));
                }
            } catch (\Exception $e) {
                // Continue even if supplier notification fails
                \Log::error('Failed to send supplier notification: ' . $e->getMessage());
            }

            try {
                foreach ($adminUsers as $adminUser) {
                    Notification::send($adminUser, new ProblemListNotification($notificationData));
                }
            } catch (\Exception $e) {
                // Continue even if admin notifications fail
                \Log::error('Failed to send admin notifications: ' . $e->getMessage());
            }

            try {
                if ($loggedInUser) {
                    Notification::send($loggedInUser, new ProblemListNotification($notificationData));
                }
            } catch (\Exception $e) {
                // Continue even if user notification fails
                \Log::error('Failed to send user notification: ' . $e->getMessage());
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'BNF berhasil ditambahkan',
                'data' => $problem
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menambahkan Problem List: ' . $e->getMessage()
            ];
        }
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

    public function uploadCar($data)
    {
        DB::beginTransaction();

        try {
            $problemList = ProblemList::findOrFail($data->problem_id);

            // Store the file in storage/app/public/car_files directory
            // Create directory if it doesn't exist
            $uploadPath = public_path('upload/img/car_files');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Get the original file name and create unique name
            $file = $data['car_file'];
            $fileName = 'CAR' . '-' . $data['no_car'] . '-' .time() . '_' . $file->getClientOriginalName();

            // Move the uploaded file to the created directory
            $file->move($uploadPath, $fileName);

            $path = 'upload/img/car_files/' . $fileName;

            // Only save the relative path without full URL
            $problemList->update([
                'no_car' => $data['no_car'],
                'car_upload_at' =>now()->setTimezone('Asia/Jakarta'),
                'car_file' => $path, // Stores path like 'car_files/filename.pdf'
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'File CAR Berhasil di uplaod!',
                'data' => $problemList
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal Upload File CAR: ' . $e->getMessage()
            ];
        }
    }

    public function uploadA3Report($data)
    {
        DB::beginTransaction();

        try {
            $problemList = ProblemList::findOrFail($data->problem_id);

            // Store the file in storage/app/public/car_files directory
            // if (!file_exists(storage_path('app/public/a3_report'))) {
            //     mkdir(storage_path('app/public/a3_report'), 0777, true);
            // }
            // $path = $data['a3_report']->store('a3_report', 'public');

            $uploadPath = public_path('upload/img/a3_report');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Get the original file name and create unique name
            $file = $data['a3_report'];
            $fileName = 'A3Report' . '-' . $data['no_a3_report'] . '-' .time() . '_' . $file->getClientOriginalName();

            // Move the uploaded file to the created directory
            $file->move($uploadPath, $fileName);

            $path = 'upload/img/a3_report/' . $fileName;

            // Only save the relative path without full URL
            $problemList->update([
                'no_a3_report' => $data['no_a3_report'],
                'report_upload_at' => now()->setTimezone('Asia/Jakarta'),
                'a3_report' => $path, // Stores path like 'car_files/filename.pdf'
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'File A3 Report Berhasil di uplaod!',
                'data' => $problemList
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal Upload File A3 Report: ' . $e->getMessage()
            ];
        }
    }

    public function selesaikan($id)
    {
        DB::beginTransaction();

        try {
            $problemList = ProblemList::findOrFail($id);

            $problemList->update([
                'status' => 'resolved',
                'updated_at' => now()->setTimezone('Asia/Jakarta'),
                'updated_by' => auth()->user()->id,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Problem List berhasil diselesaikan',
                'data' => $problemList
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal Menyelesaikan Problem List: ' . $e->getMessage()
            ];
        }
    }
}
