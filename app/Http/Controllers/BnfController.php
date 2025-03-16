<?php

namespace App\Http\Controllers;

use App\Models\Bnf;
use App\Services\BnfService;
use Illuminate\Http\Request;

class BnfController extends Controller
{
    protected $bnfService;

    public function __construct(BnfService $bnfService)
    {
        $this->middleware('can:read parts');
        $this->bnfService = $bnfService;
    }

    public function index(Request $request)
    {
        $title = 'Bad News First';
        if ($request->ajax()) {
            return $this->bnfService->datatable();
        }

        return view('bnf.index', compact('title'));
    }

    public function create()
    {
        $title = 'Create Bad News First';
        return view('bnf.create', compact('title'));
    }

    public function store(Request $request)
    {
        $result = $this->bnfService->create($request->all());

        if ($result['success']) {
            return redirect()->route('bad-news-firsts.index')->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function selesaikan(string $id)
    {
        $result = $this->bnfService->selesaikan($id);

        return response()->json($result);
    }
}
