<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SparePartController extends Controller
{
    public function index(Request $request)
    {
        $query = SparePart::query();


        // saya pakai yang contains bukan yang search bagian depan nya saja karena, 
        // menurut saya jika untuk inventory barang yang sangat banyak, 
        // ada orang yang mungkin lupa nama lengkap nya, hanya ingat bagian tengah kata atau kadang hanya ingat akhiran 
        // misal "yang belakangnya ng pokoknya", nah maka dari itu saya putuskan untuk search saya pakai contains 
        // biar bisa memudahkan  orang yang lupa nama barangnya apa
        
        # filter input text base input section
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('part_name', 'like', "%$search%")
                  ->orWhere('part_code', 'like', "%$search%");
            });
    }


        # filter dropdown base category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        
        # filter dropdown base status

        if ($request->filled('status')) {
            if ($request->status === 'critical') {
                $query->whereColumn('current_stock', '<=', 'minimum_stock');
            } elseif ($request->status === 'safe') {
                $query->whereColumn('current_stock', '>', 'minimum_stock');
            }
        }

        # format hasilnya
        $parts = $query->get()->map(function ($part) {
        return [
                'id'        => $part->id,
                'part_code' => $part->part_code,
                'part_name' => $part->part_name,
                'category'  => $part->category,
                'stock'     => $part->current_stock, 
                'min_stock' => $part->minimum_stock, 
                'status'    => $part->current_stock <= $part->minimum_stock ? 'critical' : 'safe',
            ];
        });

        return response()->json(['data' => $parts]);
    }

    public function getCategories()
{
    $categories = \App\Models\SparePart::select('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');
        

    return response()->json([
        'data' => $categories
    ]);
}

public function getUpdates(Request $request)
{
    $since = $request->query('since');

    // tentukan kolom waktu mana yang digunakan
    $timeColumn = Schema::hasColumn('spare_parts', 'last_updated')
        ? 'last_updated'
        : (Schema::hasColumn('spare_parts', 'updated_at') ? 'updated_at' : null);

    if (!$timeColumn) {
        return response()->json(['updated' => []]);
    }

    // ambil spare part yang berubah setelah waktu tertentu
    $updatedParts = \App\Models\SparePart::where($timeColumn, '>', $since)
        ->get(['id', 'part_code', 'part_name', 'current_stock', 'minimum_stock', $timeColumn]);

    // ubah format respons biar sesuai dengan frontend
    $response = $updatedParts->map(function ($part) {
        return [
            'id' => $part->id,
            'part_code' => $part->part_code,
            'part_name' => $part->part_name,
            'current_stock' => $part->current_stock,
            'minimum_stock' => $part->minimum_stock,
            'status' => $part->current_stock <= $part->minimum_stock ? 'critical' : 'safe',
        ];
    });

    return response()->json(['updated' => $response]);
}


}
