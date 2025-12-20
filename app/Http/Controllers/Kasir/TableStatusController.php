<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableStatusController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $status = $request->query('status'); // available / occupied / reserved / inactive / null

        $query = CafeTable::where('outlet_id', 1);

        if ($status) {
            $query->where('status', $status);
        }

        $tables = $query->orderBy('name')->get();

        return view('kasir.tables.index', [
            'tables' => $tables,
            'status' => $status,
        ]);
    }
}
