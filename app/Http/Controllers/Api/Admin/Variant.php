<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\ProductsModel;
use App\Models\VariantModel;
use App\Http\Requests\ValidationIndexVariant;
use App\Http\Resources\VariantCollectionResource;
use App\Http\Resources\VariantResource;
use App\Http\Requests\ValidationAddVariantRequest;


class Variant extends Controller
{

      protected $VariantModel;
      public function __construct(VariantModel $VariantModel) {
        $this->VariantModel = $VariantModel;
      }

       // Contoh URL penggunaan:
        // ?search=Kilogram              -> cari berdasarkan menu
        // ?per_page=20                 -> jumlah data per halaman
        // ?search=admin&per_page=10    -> pencarian + pagination
        // ?sort_by=menu&sort_dir=asc   -> sorting berdasarkan kolom
        // ?only_deleted=true           -> hanya tampilkan soft deleted


      public function index(ValidationIndexVariant $request) {
         
         $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $perPage = is_numeric($validated['per_page'] ?? null) ? $validated['per_page'] : 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $onlyDeleted = $validated['only_deleted'] ?? false;

        $query = $this->VariantModel
            ->select('product_variants.*','products.name as name_product')
            ->leftJoin('products','products.id','=','product_variants.product_id')
            ->onlyDeleted($onlyDeleted)
            ->search($search)
            ->sort($sortBy, $sortDir);
        $results = $query->paginate($perPage);
        $message = $results->isEmpty() ? "Data yang Anda cari tidak ditemukan" : "Success";
        return ApiResponse::paginate(new VariantCollectionResource($results), $message);
      }


      public function store(ValidationAddVariantRequest $request)  {
              $data = $request->validated();

              try {
        // Cek duplikat jika ada
        $errors = VariantModel::isDuplicate($data); 
                if (!empty($errors)) {
                 return ApiResponse::error('Validasi gagal', $errors, 400);
            }


        // Simpan data awal
       $insertedVariant = $this->VariantModel->create([
            'product_id' => $data['product_id'],
            'name'       => $data['name'],
        ]);

        // Ambil ulang varian yang baru disimpan dengan relasi produk (untuk resource)
        $variant = $this->VariantModel
                        ->select(
                            'product_variants.*',
                            'products.name as name_product'
                        )
                        ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
                        ->where('product_variants.id', $insertedVariant->id)
                        ->first();

        return ApiResponse::success(new VariantResource($variant), 'Success Create New Variant', 201);
    } catch (\Illuminate\Database\QueryException $e) {
        return ApiResponse::error('Gagal membuat variant baru (query error)', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 422);
    } catch (\Exception $e) {
        return ApiResponse::error('Terjadi kesalahan saat membuat variant baru', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
      }

       public function show(string $id)
        {
           
             $variant = $this->VariantModel
            ->select(
                            'product_variants.*',
                            'products.name as name_product'
                        )
                        ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.id', $id)
            ->first();
            if (!$variant) {
                return ApiResponse::error('Variant not found', [
                    'id' => ['Data dengan ID tersebut tidak tersedia']
                ], 404);
            }
            return ApiResponse::success(new VariantResource($variant), 'Success ambil Variant detail', 200);
        }

      



        public function update(ValidationAddVariantRequest $request, $id)
            {
                $data = $request->validated();

                $variant = $this->VariantModel
                        ->select(
                                        'product_variants.*',
                                        'products.name as name_product'
                                    )
                                    ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
                        ->where('product_variants.id', $id)
                        ->first();

                if (!$variant) {
                    return ApiResponse::error('product dengan ID tersebut tidak ditemukan.', [
                            'id' => ['Data tidak tersedia.']
                        ], 404);
                }

            try {
                // Validasi duplikat
                $errors = VariantModel::isDuplicate($data, $id);
                        if (!empty($errors)) {
                        return ApiResponse::error('Validasi gagal', $errors, 400);
                    }
                $variant->update($data);

                return ApiResponse::success(new VariantResource($variant), 'Berhasil memperbarui Variant', 200);
            } catch (\Illuminate\Database\QueryException $e) {
                return ApiResponse::error('Gagal update Variant (query error)', [
                    'exception' => config('app.debug') ? $e->getMessage() : null
                ], 422);
            } catch (\Exception $e) {
                return ApiResponse::error('Terjadi kesalahan saat update Variant', [
                    'exception' => config('app.debug') ? $e->getMessage() : 'Silakan coba beberapa saat lagi'
                ], 500);
            }
        }



public function destroy(string $id)
{
    try {
         $variant = $this->VariantModel
            ->select(
                            'product_variants.*',
                            'products.name as name_product'
                        )
                        ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.id', $id)
            ->first();


        if (!$variant) {
              return ApiResponse::error('product dengan ID tersebut tidak ditemukan.', [
                'id' => ['Data tidak tersedia.']
            ], 404);
        }

        $variant->delete();

        return ApiResponse::success(new VariantResource($variant), 'Variant Product berhasil dihapus.', 200);
    } catch (\Exception $e) {
        return ApiResponse::error('Gagal menghapus Variant Product .', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

}
