<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use App\Http\Requests\ValidationIndexProduct;
use App\Models\ProductsModel;
use App\Http\Resources\ProductCollectionResource;
use App\Http\Resources\ProductResource;
use App\Http\Requests\ProductsAddValidationRequest;

class Products extends Controller
{

      protected $ProductsModel;
      public function __construct(ProductsModel $ProductsModel) {
        $this->ProductsModel = $ProductsModel;
      }



    public function index(ValidationIndexProduct $request)  {
         $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $perPage = is_numeric($validated['per_page'] ?? null) ? $validated['per_page'] : 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $onlyDeleted = $validated['only_deleted'] ?? false;

       $query = $this->ProductsModel
        ->select('products.*', 'products.name as name_product',
            DB::raw('(SELECT image_url FROM product_images WHERE product_images.product_id = products.id LIMIT 1) AS image_url'),
            DB::raw('(SELECT name as variant FROM product_variants WHERE product_variants.product_id = products.id LIMIT 1) AS variant'),
            'categories.name as name_category'
        )
        ->leftJoin('categories','categories.id', '=', 'products.category_id')
        ->onlyDeleted($onlyDeleted)
        ->search($search)
        ->sort($sortBy, $sortDir);


        $results = $query->paginate($perPage);
        $message = $results->isEmpty() ? "Data yang Anda cari tidak ditemukan" : "Success";
        return ApiResponse::paginate(new ProductCollectionResource($results), $message);
    }


public function select()
{
  $products = $this->ProductsModel
            ->select('id', 'name') // Perbaiki di sini
            ->get();
            
        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil diambil',
            'data' => $products
        ]);
}



 



    public function show(string $id)
        {

            $Products = $this->ProductsModel
            ->select('products.*', 'products.name as name_product',
                DB::raw('(SELECT image_url FROM product_images WHERE product_images.product_id = products.id LIMIT 1) AS image_url'),
                DB::raw('(SELECT name FROM product_variants WHERE product_variants.product_id = products.id LIMIT 1) AS variant'),
                'categories.name as name_category'
            )
            ->leftJoin('categories','categories.id','=','products.category_id')
            ->where('products.id', $id)
            ->first();

            if (!$Products) {
                return ApiResponse::error('', [
                    'id' => ['Data dengan ID tersebut tidak tersedia']
                ], 404);
            }

            return ApiResponse::success(new ProductResource($Products), 'Success get product detail', 200);
            
        }



  public function store(ProductsAddValidationRequest $request)
{
    $data = $request->validated();

    try {
        // Cek duplikat jika ada
        $errors = ProductsModel::isDuplicate($data); 
                if (!empty($errors)) {
                 return ApiResponse::error('Validasi gagal', $errors, 400);
            }


        // Simpan data awal
        $insertedProduct = $this->ProductsModel->create([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']), // slug dibuat otomatis dari nama
            'description' => $data['description'],
            'price'       => $data['price'],
            'stock'       => $data['stock'],
            'status'      => $data['status'],
        ]);

        // Ambil ulang dengan join agar field seperti name_product, name_category, image_url terisi
        $product = $this->ProductsModel
            ->select(
                'products.*',
                'products.name as name_product',
                DB::raw('(SELECT name FROM categories WHERE categories.id = products.category_id LIMIT 1) AS name_category'),
                DB::raw('(SELECT image_url FROM product_images WHERE product_images.product_id = products.id LIMIT 1) AS image_url'),
                DB::raw('(SELECT name FROM product_variants WHERE product_variants.product_id = products.id LIMIT 1) AS variant')
            )
            ->where('products.id', $insertedProduct->id)
            ->first();

        return ApiResponse::success(new ProductResource($product), 'Success Create New product', 201);
    } catch (\Illuminate\Database\QueryException $e) {
        return ApiResponse::error('Gagal membuat product (query error)', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 422);
    } catch (\Exception $e) {
        return ApiResponse::error('Terjadi kesalahan saat membuat product', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

public function update(ProductsAddValidationRequest $request, $id)
{
    $data = $request->validated();

    $product = $this->ProductsModel
    ->select('products.*', 'products.name as name_product',
        DB::raw('(SELECT image_url FROM product_images WHERE product_images.product_id = products.id LIMIT 1) AS image_url'),
        DB::raw('(SELECT name as variant FROM product_variants WHERE product_variants.product_id = products.id LIMIT 1) AS variant'),
        'categories.name as name_category'
    )
    ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
    ->where('products.id', $id)
    ->first();

    if (!$product) {
           return ApiResponse::error('product dengan ID tersebut tidak ditemukan.', [
                'id' => ['Data tidak tersedia.']
            ], 404);
    }

    try {

         // Validasi duplikat
        $errors = ProductsModel::isDuplicate($data, $id);
                if (!empty($errors)) {
                 return ApiResponse::error('Validasi gagal', $errors, 400);
            }


        // Auto-generate slug dari nama produk
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);

        return ApiResponse::success(new ProductResource($product), 'Berhasil memperbarui product', 200);
    } catch (\Illuminate\Database\QueryException $e) {
        return ApiResponse::error('Gagal update product (query error)', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 422);
    } catch (\Exception $e) {
        return ApiResponse::error('Terjadi kesalahan saat update product', [
            'exception' => config('app.debug') ? $e->getMessage() : 'Silakan coba beberapa saat lagi'
        ], 500);
    }
}


public function destroy(string $id)
{
    try {
        $product = $this->ProductsModel
    ->select('products.*', 'products.name as name_product',
        DB::raw('(SELECT image_url FROM product_images WHERE product_images.product_id = products.id LIMIT 1) AS image_url'),
        DB::raw('(SELECT name as variant FROM product_variants WHERE product_variants.product_id = products.id LIMIT 1) AS variant'),
        'categories.name as name_category'
    )
    ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
    ->where('products.id', $id)
    ->first();

        if (!$product) {
              return ApiResponse::error('product dengan ID tersebut tidak ditemukan.', [
                'id' => ['Data tidak tersedia.']
            ], 404);
        }

        $product->delete();

        return ApiResponse::success(new ProductResource($product), 'product berhasil dihapus.', 200);
    } catch (\Exception $e) {
        return ApiResponse::error('Gagal menghapus product.', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}




}
