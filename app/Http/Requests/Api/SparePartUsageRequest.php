<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SparePartUsageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
        'machine_id'    => ['required', 'integer', 'exists:machines,id'],
        'spare_part_id' => ['required', 'integer', 'exists:spare_parts,id'],
        'quantity_used' => ['required', 'integer', 'min:1'], 
        'notes'         => ['required', 'string'], 
        'recorded_by'   => ['required', 'string'],
    ];
    }

    public function messages(): array 
{
    return [
        'machine_id.required'    => 'Machine wajib diisi.',
        'machine_id.exists'      => 'Machine tidak ditemukan di database.',
        'spare_part_id.required' => 'Spare part wajib diisi.',
        'spare_part_id.exists'   => 'Spare part tidak ditemukan di database.',
        'quantity_used.required' => 'Quantity wajib diisi.',
        'quantity_used.min'      => 'Quantity harus bernilai positif.',
        'notes.required'         => 'Catatan (notes) wajib diisi.',
        'recorded_by.required'   => 'Nama pencatat wajib diisi.',
    ];
}
}
