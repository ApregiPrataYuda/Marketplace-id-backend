<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductsAddValidationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

      public function rules(): array
{
    return [
        'category_id'  => 'required|integer|exists:categories,id',
        'name'         => 'required|string|max:255',
        'description'  => 'required|string',
        'price'        => 'required|numeric|min:0',
        'stock'        => 'required|integer|min:0',
        'status'       => 'required|in:active,inactive',
    ];
}

public function messages(): array
{
    return [
        'category_id.required' => 'Kategori wajib dipilih.',
        'category_id.integer'  => 'Kategori tidak valid.',
        'name.required'        => 'Nama produk wajib diisi.',
        'description.required' => 'Deskripsi wajib diisi.',
        'price.required'       => 'Harga wajib diisi.',
        'price.numeric'        => 'Harga harus berupa angka.',
        'stock.required'       => 'Stok wajib diisi.',
        'stock.integer'        => 'Stok harus berupa angka.',
        'status.required'      => 'Status wajib diisi.',
        'status.in'            => 'Status harus "active" atau "inactive".',
    ];
}

}
