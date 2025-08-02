<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ValidationAddProductImages extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],

           'image' => 'nullable|array',
           'image.*' => 'file|mimes:jpeg,png,jpg,gif|max:2048',

            'is_primary' => [
                'required',
                'integer',
                'in:0,1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.integer' => 'Produk tidak valid.',
            'product_id.exists' => 'Produk yang dipilih tidak ditemukan.',
            
            'images.required' => 'Minimal 1 gambar harus diunggah.',
            'images.*.image' => 'File harus berupa gambar.',
            'images.*.mimes' => 'Gambar harus berformat jpeg, png, jpg, atau gif.',
            'images.*.max' => 'Ukuran gambar maksimal 2MB.',
            
            'is_primary.required' => 'Status gambar utama wajib diisi.',
            'is_primary.integer' => 'Nilai gambar utama harus berupa angka 0 atau 1.',
        ];
    }

}
