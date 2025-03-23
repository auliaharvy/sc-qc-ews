<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->middleware('can:read suppliers');
        $this->supplierService = $supplierService;
    }

    public function index(Request $request)
    {
        $title = 'Suppliers';
        if ($request->ajax()) {
            return $this->supplierService->datatable();
        }

        return view('suppliers.index', compact('title'));
    }

    public function create()
    {
        $title = 'Tambah Supplier';
        return view('suppliers.create', compact('title'));
    }

    public function store(Request $request)
    {
        $result = $this->supplierService->create($request->all());

        if ($result['success']) {
            return redirect()->route('suppliers.index')->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    public function edit(string $id)
    {
        $title = 'Edit Supplier';
        $supplier = $this->supplierService->getById($id);

        return view('suppliers.edit', compact('title', 'supplier'));
    }

    public function update(Request $request, string $id)
    {
        $result = $this->supplierService->update($request->all(), $id);

        if ($result['success']) {
            return redirect()->route('suppliers.index')->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus');
    }
}
