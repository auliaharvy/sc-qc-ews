<?php

namespace App\Http\Controllers;

use App\Models\ProblemList;
use App\Services\ProblemListService;
use Illuminate\Http\Request;

class ProblemListController extends Controller
{
    protected $problemListService;

    public function __construct(ProblemListService $problemListService)
    {
        $this->middleware('can:read parts');
        $this->problemListService = $problemListService;
    }

    public function index(Request $request)
    {
        $title = 'List Claim';
        if ($request->ajax()) {
            return $this->problemListService->datatable();
        }

        return view('problem.index', compact('title'));
    }

    public function create()
    {
        $title = 'Create Problem List';
        return view('problem.create', compact('title'));
    }

    public function store(Request $request)
    {
        $result = $this->problemListService->create($request->all());

        if ($result['success']) {
            return redirect()->route('problems.index')->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function uploadCar(Request $request)
    {
        $result = $this->problemListService->uploadCar($request);

        return response()->json($result);
    }

    public function uploadA3Report(Request $request)
    {
        $result = $this->problemListService->uploadA3Report($request);

        return response()->json($result);
    }

    public function selesaikan(string $id)
    {
        $result = $this->problemListService->selesaikan($id);

        return response()->json($result);
    }
}
