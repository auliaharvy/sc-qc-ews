<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\BnfNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\Bnf;
use App\Models\Part;
use App\Models\Supplier;

class BnfService
{
    public function dataTable()
    {
        $userRole = auth()->user()->roles()->first()->name;
        $supplierId = auth()->user()->supplier_id;
        $request = request();

$query = Bnf::with(['supplier', 'part'])->select('bad_news_first.*');

       

        if ($userRole == 'Admin Supplier') {
    $query = Bnf::with(['supplier', 'part'])->select('bad_news_first.*')->where('supplier_id', $supplierId);
        } 

        // Apply filters from request
        if ($request->filled('supplier_id')) {
    $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('part_id')) {
    $query->where('part_id', $request->part_id);
        }

        if ($request->filled('problem')) {
    $query->where('problem', $request->problem);
        }

        if ($request->filled('status')) {
     $query->where('status', $request->input('status'));
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('part_name', function($row) {
                return $row->part->part_name ?? '-';
            })
            ->addColumn('supplier_name', function($row) {
                return $row->supplier->name ?? '-';
            })
            
            ->addColumn('status', function($row) {
                return $row->status == 'open' ? '<span class="badge bg-danger text-white">Belum Selesai</span>' : '<span class="badge bg-success">Selesai</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->status == 'open') {
                    $actionBtn = '';
                    if (auth()->user()->id == $row->created_by) {
                        $actionBtn .= '<a href="' . route('parts.edit', $row->id) . '"
                                class="edit btn btn-warning btn-sm me-2"><i class="ti-pencil-alt"></i></a>';
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
                return $row->created_at->format('d M Y H:i');
            })
            ->rawColumns(['action', 'status'])
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
            $bnf = Bnf::create([
                'part_id' => $data['part_id'],
                'supplier_id' => $data['supplier_id'],
                'problem' => $data['problem'],
                'qty' => $data['qty'],
                'issuance_date' => $data['issuance_date'],
                'description' => $data['description']
            ]);



            $supplier = Supplier::find($data['supplier_id']);
            $part = Part::find($data['part_id']);

            $adminUsers = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            $loggedInUser = auth()->user();

            $notificationData = [
                'supplier' => $supplier->name,
                'part_number' => $part->part_number,
                'part_name' => $part->part_name,
                'problem' => $data['problem'],
                'qty' => $data['qty'],
                'created_by' => auth()->user()->id,
                'issuance_date' => $data['issuance_date'],
                'description' => $data['description']
            ];

            try {
                if ($supplier) {
                    // Notification::send($supplier, new BnfNotification($notificationData));
                    Notification::route('mail', $supplier->email)->notify(new BnfNotification($notificationData));
                }
            } catch (\Exception $e) {
                // Continue even if supplier notification fails
                \Log::error('Failed to send supplier notification: ' . $e->getMessage());
            }

            try {
                foreach ($adminUsers as $adminUser) {
                    Notification::send($adminUser, new BnfNotification($notificationData));
                }
            } catch (\Exception $e) {
                // Continue even if admin notifications fail
                \Log::error('Failed to send admin notifications: ' . $e->getMessage());
            }

            try {
                if ($loggedInUser) {
                    Notification::send($loggedInUser, new BnfNotification($notificationData));
                }
            } catch (\Exception $e) {
                // Continue even if user notification fails
                \Log::error('Failed to send user notification: ' . $e->getMessage());
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'BNF berhasil ditambahkan',
                'data' => $bnf
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menambahkan BNF: ' . $e->getMessage()
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

    public function selesaikan($id)
    {
        DB::beginTransaction();

        try {
            $bnf = Bnf::findOrFail($id);

            $bnf->update([
                'status' => 'resolved',
                'completion_date' => now()->setTimezone('Asia/Jakarta'),
                'finish_by' => auth()->user()->id,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'BNF berhasil diselesaikan',
                'data' => $bnf
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal Menyelesaikan BNF: ' . $e->getMessage()
            ];
        }
    }
}
