<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScanService;

class ScanController extends Controller
{
    public function __construct(protected ScanService $scan)
    {
    }

    public function page()
    { 
        return view('public.scan.page'); 
    }

    public function scan(Request $request)
    {
        $data = $request->validate(['code' => 'required']);
        $result = $this->scan->scanByCode($data['code'], $request->user()?->id);
        return response()->json($result['payload'], $result['status']);
    }

    public function fragment(string $code)
    {
        $html = $this->scan->renderFragment($code);
        return response()->json(['html' => $html]);
    }
}
