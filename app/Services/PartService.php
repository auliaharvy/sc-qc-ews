<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\Part;

class PartService
{
    public function dataTable()
    {
        $userRole = auth()->user()->roles()->first()->name;
        $supplierId = auth()->user()->supplier_id;

        if ($userRole == 'Admin Supplier') {
            $data = Part::with('supplier')->select('parts.*')->where('supplier_id', $supplierId);
        } else {
            $data = Part::with('supplier')->select('parts.*');
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('supplier_name', function($row) {
                return $row->supplier->name ?? '-';
            })
            ->addColumn('action', function ($row) {
                $actionBtn = '<a href="' . route('parts.edit', $row->id) . '"
                            class="edit btn btn-warning btn-sm me-2"><i class="ti-pencil-alt"></i></a>';
                return '<div class="d-flex">' . $actionBtn . '</div>';
            })
            ->editColumn('created_at', function($row) {
                return $row->created_at->format('d M Y H:i');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getById($id)
    {
        return Part::with('supplier')->findOrFail($id);
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $part = Part::create([
                'supplier_id' => $data['supplier_id'],
                'part_number' => $data['part_number'],
                'part_name' => $data['part_name'],
                'code' => $this->generatePartCode(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Part berhasil ditambahkan',
                'data' => $part
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menambahkan part: ' . $e->getMessage()
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
