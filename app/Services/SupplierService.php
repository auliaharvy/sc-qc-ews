<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\Supplier;

class SupplierService
{
    public function dataTable()
    {
        $data = Supplier::query();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = '<a href="' . route('suppliers.edit', $row->id) . '"
                            class="edit btn btn-warning btn-sm me-2"><i class="ti-pencil-alt"></i></a>';
                $actionBtn .= '<button type="button"
                            data-id="' . $row->id . '"
                            class="deleteSupplier btn btn-danger btn-sm">
                            <i class="ti-trash"></i></button>';
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
        return User::findOrFail($id);
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $supplier = Supplier::create([
                'code' => $this->generateSupplierCode(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'contact_person' => $data['contact_person'],
                'website' => $data['website'] ?? null,
                'status' => $data['status'] ?? 'active'
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Supplier berhasil ditambahkan',
                'data' => $supplier
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menambahkan supplier: ' . $e->getMessage()
            ];
        }
    }

    private function generateSupplierCode()
    {
        $latest = Supplier::latest()->first();
        $sequence = $latest ? intval(substr($latest->code, 3)) + 1 : 1;
        return 'SPL' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function update($data, $id)
    {
        DB::beginTransaction();

        try {
            $supplier = Supplier::findOrFail($id);

            $supplier->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'contact_person' => $data['contact_person'],
                'website' => $data['website'] ?? null,
                'status' => $data['status'] ?? 'active'
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Supplier berhasil diperbarui',
                'data' => $supplier
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal memperbarui supplier: ' . $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            // find user
            $user = User::find($id);

            // find user profile
            $userProfile = UserProfile::where('user_id', $id)->first();

            if ($user) {

                // delete user
                $this->deleteUser($user);

                // delete user profile
                $this->deleteUserProfile($userProfile);

                // delete user roles
                $user->roles()->detach();

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

    public function deleteUser($user)
    {
        return $user->delete();
    }

    public function deleteUserProfile($userProfile)
    {
        $imagePath = null;
        if ($userProfile->image) {
            $imagePath = public_path('assets/images/users/' . $userProfile->image);
        }

        $userProfile->delete();

        if ($imagePath && file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
