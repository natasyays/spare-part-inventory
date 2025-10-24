<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SparePartUsageRequest;
use App\Models\SparePart;
use App\Models\SparePartUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class SparePartUsageController extends Controller
{
    public function store(SparePartUsageRequest $request)
    {

        
        # ambil data hasil validasi dari form request
        $data = $request->validated();

        try {
            DB::beginTransaction();

            # catat penggunaan di tabel spare_part_usages
            $usage = SparePartUsage::create([
                'machine_id'    => $data['machine_id'],
                'spare_part_id' => $data['spare_part_id'],
                'quantity_used' => $data['quantity_used'],
                'notes'         => $data['notes'],
                'recorded_by'   => $data['recorded_by'],
                'usage_date' => now(),
            ]);

            # update stock di tabel spare_parts
            $sparePart = SparePart::findOrFail($request->spare_part_id);

            # cek kesediaan stoknya
            if ($sparePart->current_stock < $request->quantity_used) {
                return response()->json(['message' => 'Stok tidak mencukupi untuk spare part tersebut.'], 400);
            }

            $sparePart->current_stock -= $data['quantity_used'];
            $sparePart->save();

            # catat history di table stock_history dengan reason usage
            DB::table('stock_history')->insert([
                'spare_part_id'     => $data['spare_part_id'],
                'old_stock'         => $sparePart->current_stock + $data['quantity_used'], // stok sebelum dipakai
                'new_stock'         => $sparePart->current_stock, // stok setelah dipakai
                'reason'            => 'usage',
                'quantity_changed'  => -$data['quantity_used'],
                'changed_by'        => $data['recorded_by'],
                'changed_date'      => now(),
            ]);

            DB::commit();

            # return response sukses
            return response()->json([
                'message' => 'Data usage yang baru dibuat.',
                'data' => $usage,
            ], 201);
            
            # return response gagal
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 400);
        }
    }
}
