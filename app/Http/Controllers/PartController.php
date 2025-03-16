<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Services\PartService;
use Illuminate\Http\Request;

class PartController extends Controller
{
    protected $partService;

    public function __construct(PartService $partService)
    {
        $this->middleware('can:read parts');
        $this->partService = $partService;
    }

    public function index(Request $request)
    {
        $title = 'Parts';
        if ($request->ajax()) {
            return $this->partService->datatable();
        }

        return view('parts.index', compact('title'));
    }

    public function create()
    {
        $title = 'Create Parts';
        return view('parts.create', compact('title'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number' => 'required|unique:parts,part_number',
            'part_name' => 'required',
            'supplier_id' => 'required',
            'model' => '',
            'sebango' => '',
        ]);

        Part::create($validated);
        return redirect()->route('parts.index')->with('success', 'Part berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $title = 'Create Parts';
        $part = $this->partService->getById($id);
        return view('parts.edit', compact('part', 'title'));
    }

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'part_number' => 'required',
            // 'part_id' => 'required|unique:parts,id,',
            'part_name' => 'required',
            'supplier_id' => 'required',
            'model' => '',
            'sebango' => '',
        ]);

        $part->update($validated);
        return redirect()->route('parts.index')->with('success', 'Part berhasil diperbarui');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus');
    }
}
