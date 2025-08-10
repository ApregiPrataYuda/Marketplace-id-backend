<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Requests\ValidationIndexTagsProduct;
use App\Http\Resources\ProductTagsResource;
use App\Http\Resources\ProductTagsCollection;
use App\Models\ProductTagsModel;
use App\Http\Requests\ValidationAddProductTagsIndex;

class ProductTags extends Controller
{   
    
    protected $ProductTagsModel;
    public function __construct(ProductTagsModel $ProductTagsModel) {
        $this->ProductTagsModel = $ProductTagsModel;
    }


    public function index(ValidationIndexTagsProduct $request)  {
        $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $perPage = is_numeric($validated['per_page'] ?? null) ? $validated['per_page'] : 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $onlyDeleted = $validated['only_deleted'] ?? false;

        $query = $this->ProductTagsModel
            ->select('product_tag.*','tags.name as name_tags','products.name as name_product')
            ->leftJoin('tags','tags.id','=','product_tag.tag_id')
            ->leftJoin('products','products.id','=','product_tag.product_id')
            ->onlyDeleted($onlyDeleted)
            ->search($search) 
            ->sort($sortBy, $sortDir);
        $results = $query->paginate($perPage);
        $message = $results->isEmpty() ? "Data yang Anda cari tidak ditemukan" : "Success";
        return ApiResponse::paginate(new ProductTagsCollection($results), $message);
    }



     public function store(ValidationAddProductTagsIndex $request)  {
              $data = $request->validated();
              try {
        // Cek duplikat jika ada
        $errors = ProductTagsModel::isDuplicate($data); 
                if (!empty($errors)) {
                 return ApiResponse::error('Validasi gagal', $errors, 400);
            }
        // Simpan data awal
       $insertedProductTags = $this->ProductTagsModel->create([
            'product_id' => $data['product_id'],
            'tag_id'       => $data['tag_id'],
        ]);
        // Ambil ulang varian yang baru disimpan dengan relasi produk (untuk resource)
        $productTags = $this->ProductTagsModel
                        ->select('product_tag.*','tags.name as name_tags','products.name as name_product')
                        ->leftJoin('tags','tags.id','=','product_tag.tag_id')
                        ->leftJoin('products','products.id','=','product_tag.product_id')
                        ->where('product_tag.id', $insertedProductTags->id)
                        ->first();

        return ApiResponse::success(new ProductTagsResource($productTags), 'Success Create New Variant', 201);
    } catch (\Illuminate\Database\QueryException $e) {
        return ApiResponse::error('Gagal membuat product tags baru (query error)', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 422);
    } catch (\Exception $e) {
        return ApiResponse::error('Terjadi kesalahan saat membuat product tags baru', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
      }


       public function show(string $id)
        {
            $productTags = $this->ProductTagsModel
                        ->select('product_tag.*','tags.name as name_tags','products.name as name_product')
                        ->leftJoin('tags','tags.id','=','product_tag.tag_id')
                        ->leftJoin('products','products.id','=','product_tag.product_id')
                        ->where('product_tag.id', $id)
                        ->first();
            if (!$productTags) {
                return ApiResponse::error('Variant not found', [
                    'id' => ['Data dengan ID tersebut tidak tersedia']
                ], 404);
            }
            return ApiResponse::success(new ProductTagsResource($productTags), 'Success ambil Variant detail', 200);
        }


        public function update(ValidationAddProductTagsIndex $request, $id)
            {
                $data = $request->validated();

                  $productTags = $this->ProductTagsModel
                        ->select('product_tag.*','tags.name as name_tags','products.name as name_product')
                        ->leftJoin('tags','tags.id','=','product_tag.tag_id')
                        ->leftJoin('products','products.id','=','product_tag.product_id')
                        ->where('product_tag.id', $id)
                        ->first();

                if (!$productTags) {
                    return ApiResponse::error('Tags product dengan ID tersebut tidak ditemukan.', [
                            'id' => ['Data tidak tersedia.']
                        ], 404);
                }

            try {
                // Validasi duplikat
                $errors = ProductTagsModel::isDuplicate($data, $id);
                        if (!empty($errors)) {
                        return ApiResponse::error('Validasi gagal', $errors, 400);
                    }
                $productTags->update($data);

                return ApiResponse::success(new ProductTagsResource($productTags), 'Berhasil memperbarui product tags', 200);
            } catch (\Illuminate\Database\QueryException $e) {
                return ApiResponse::error('Gagal update product tags (query error)', [
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
                     $productTags = $this->ProductTagsModel
                        ->select('product_tag.*','tags.name as name_tags','products.name as name_product')
                        ->leftJoin('tags','tags.id','=','product_tag.tag_id')
                        ->leftJoin('products','products.id','=','product_tag.product_id')
                        ->where('product_tag.id', $id)
                        ->first();


                    if (!$productTags) {
                        return ApiResponse::error('Tags product dengan ID tersebut tidak ditemukan.', [
                            'id' => ['Data tidak tersedia.']
                        ], 404);
                    }

                    $productTags->delete();

                    return ApiResponse::success(new ProductTagsResource($productTags), 'Tags Product berhasil dihapus.', 200);
                } catch (\Exception $e) {
                    return ApiResponse::error('Gagal menghapus Tags Product .', [
                        'exception' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }

            }


}
