<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::select('id', 'machine_name')->get();

        return response()->json([
            'data' => $machines
        ]);
    }
}
