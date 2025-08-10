<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponse;
use App\Http\Resources\ProductsImageResource;
use App\Http\Requests\ProductsImageRequestIndex;
use App\Http\Resources\ProductsImageResourceCollection;
use App\Http\Requests\ValidationAddProductImages;
use App\Models\ProductsImageModel;

class ProductsImage extends Controller
{
    protected $ProductsImageModel;
    public function __construct(ProductsImageModel $ProductsImageModel) {
        $this->ProductsImageModel = $ProductsImageModel;
    }


     // Contoh URL penggunaan:
        // ?search=Kilogram              -> cari berdasarkan menu
        // ?per_page=20                 -> jumlah data per halaman
        // ?search=admin&per_page=10    -> pencarian + pagination
        // ?sort_by=menu&sort_dir=asc   -> sorting berdasarkan kolom
        // ?only_deleted=true           -> hanya tampilkan soft deleted


         // query lama tampilkan semua data
    //    public function index(ProductsImageRequestIndex $request) {
    //         $validated = $request->validated();
    //         $search = $validated['search'] ?? null;
    //         $perPage = is_numeric($validated['per_page'] ?? null) ? $validated['per_page'] : 10;
    //         $sortBy = $validated['sort_by'] ?? 'created_at';
    //         $sortDir = $validated['sort_dir'] ?? 'desc';
    //         $onlyDeleted = $validated['only_deleted'] ?? false;

    //         $query = $this->ProductsImageModel
    //             ->select('product_images.*','MIN(product_images.id) as id','products.name as name_product')
    //             ->leftJoin('products','products.id','=','product_images.product_id')
    //             ->onlyDeleted($onlyDeleted)
    //             ->search($search)
    //             ->sort($sortBy, $sortDir);
    //         $results = $query->paginate($perPage);
    //         $message = $results->isEmpty() ? "Data yang Anda cari tidak ditemukan" : "Success";
    //         return ApiResponse::paginate(new ProductsImageResourceCollection($results), $message);
    //    }


    // query baru tampilkan 1 data saja jika ada banyak data
public function index(ProductsImageRequestIndex $request)
{
    $validated = $request->validated();
    $search = $validated['search'] ?? null;
    $perPage = is_numeric($validated['per_page'] ?? null) ? $validated['per_page'] : 10;
    $sortBy = $validated['sort_by'] ?? 'created_at';
    $sortDir = $validated['sort_dir'] ?? 'desc';
    $onlyDeleted = $validated['only_deleted'] ?? false;

    $query = $this->ProductsImageModel
        ->selectRaw('
            MIN(product_images.id) as id,
            product_images.product_id,
            products.name as name_product,
            MIN(product_images.created_at) as created_at,
            MIN(product_images.updated_at) as updated_at
        ')
        ->leftJoin('products', 'products.id', '=', 'product_images.product_id')
        ->onlyDeleted($onlyDeleted)
        ->search($search)
        ->groupBy('product_images.product_id', 'products.name')
        ->orderBy($sortBy, $sortDir);

    $results = $query->paginate($perPage);

    $message = $results->isEmpty()
        ? "Data yang Anda cari tidak ditemukan"
        : "Success";

    return ApiResponse::paginate(
        new ProductsImageResourceCollection($results),
        $message
    );
}







public function getImagesByProduct(ProductsImageRequestIndex $request, $id)
{
    $validated = $request->validated();
    $search = $validated['search'] ?? null;
    $perPage = is_numeric($validated['per_page'] ?? null) ? $validated['per_page'] : 10;
    $sortBy = $validated['sort_by'] ?? 'created_at';
    $sortDir = $validated['sort_dir'] ?? 'desc';
    $onlyDeleted = $validated['only_deleted'] ?? false;

    $query = $this->ProductsImageModel
        ->select('product_images.*', 'products.name as name_product')
        ->leftJoin('products', 'products.id', '=', 'product_images.product_id')
        ->where('products.id', $id)
        ->onlyDeleted($onlyDeleted)
        ->search($search)
        ->sort($sortBy, $sortDir);

    $results = $query->paginate($perPage);

    if ($results->isEmpty()) {
        return ApiResponse::error('Product Image not found', [
            'id' => ['Data dengan ID tersebut tidak tersedia']
        ], 404);
    }
    $message = $results->isEmpty() ? "Data yang Anda cari tidak ditemukan" : "Success";
    return ApiResponse::paginate(new ProductsImageResourceCollection($results), $message);
}






    //    api untuk simpan gambar product gambar banyak sekaligus
        public function store(ValidationAddProductImages $request)
    {
        $data = $request->validated();

        try {
            $images = [];
            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs('image/products', $filename, 'public');
                    // $images[] = $filename;
                    //   $images[] = Storage::url('image/products/' . $filename);
                      $images[] = url(Storage::url('image/products/' . $filename));

                }
            } else {
                // $images[] = 'default.jpeg';
                //  $images[] = Storage::url('image/products/default.jpg');
                 $images[] = url(Storage::url('image/products/default.jpg'));

            }

            $createdImages = [];
            foreach ($images as $index => $img) {
                $created = $this->ProductsImageModel->create([
                    'product_id' => $data['product_id'],
                    'image_url' => $img,
                    'is_primary' => ($index === 0) ? $data['is_primary'] : 0, // hanya gambar pertama yg jadi primary
                ]);
                $createdImages[] = $created;
            }

            // Ambil salah satu data untuk dikembalikan (misalnya gambar pertama)
            $productImage = $this->ProductsImageModel
                ->select('product_images.*', 'products.name as name_product')
                ->leftJoin('products', 'products.id', '=', 'product_images.product_id')
                ->where('product_images.id', $createdImages[0]->id)
                ->first();

            return ApiResponse::success(new ProductsImageResource($productImage), 'Success Upload Product Image', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return ApiResponse::error('Gagal menyimpan gambar produk (query error)', [
                'exception' => config('app.debug') ? $e->getMessage() : null
            ], 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Terjadi kesalahan saat upload gambar produk', [
                'exception' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

// simpan hanya 1 gambar saja
public function store1image(ValidationAddProductImages $request)
{
    $data = $request->validated();

    try {
        $filename = 'default.jpeg';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('image/products', $filename, 'public');
        }

        $created = $this->ProductsImageModel->create([
            'product_id' => $data['product_id'],
            'image_url' => $filename,
            'is_primary' => $data['is_primary'] ?? 0, // default jika tidak dikirim
        ]);

        $productImage = $this->ProductsImageModel
            ->select('product_images.*', 'products.name as name_product')
            ->leftJoin('products', 'products.id', '=', 'product_images.product_id')
            ->where('product_images.id', $created->id)
            ->first();

        return ApiResponse::success(new ProductsImageResource($productImage), 'Success Upload Product Image', 201);

    } catch (\Illuminate\Database\QueryException $e) {
        return ApiResponse::error('Gagal menyimpan gambar produk (query error)', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 422);
    } catch (\Exception $e) {
        return ApiResponse::error('Terjadi kesalahan saat upload gambar produk', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

    public function update(Request $request, $id)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
      'image' => 'sometimes|file|image|mimes:jpeg,png,jpg|max:2048',
        'is_primary' => 'nullable|boolean',
    ]);

    try {
        $productImage = $this->ProductsImageModel->findOrFail($id);

        // Upload file baru
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('image/products', $filename, 'public');

            // (Opsional) Hapus gambar lama dari storage
            if ($productImage->image_url !== 'default.jpeg') {
                Storage::disk('public')->delete('image/products/' . $productImage->image_url);
            }

            // Simpan nama file baru
            $productImage->image_url = $filename;
        }

          $productImage->product_id = $request->product_id;
        // Jika is_primary diset, update juga
        if ($request->has('is_primary')) {
            $productImage->is_primary = $request->is_primary;
        }

        $productImage->save();

        $productImages = $this->ProductsImageModel
            ->select('product_images.*', 'products.name as name_product')
            ->leftJoin('products', 'products.id', '=', 'product_images.product_id')
            ->where('product_images.id', $id)
            ->first();

        return ApiResponse::success(new ProductsImageResource($productImages), 'Berhasil update gambar produk');

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return ApiResponse::error('Data gambar tidak ditemukan', [], 404);
    } catch (\Exception $e) {
        return ApiResponse::error('Terjadi kesalahan saat update gambar produk', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}


public function show(string $id)
        {
            $productTags = $this->ProductsImageModel
                         ->select('product_images.*', 'products.name as name_product')
                        ->leftJoin('products', 'products.id', '=', 'product_images.product_id')
                        ->where('product_images.id', $id)
                        ->first();

            if (!$productTags) {
                return ApiResponse::error('Product Image not found', [
                    'id' => ['Data dengan ID tersebut tidak tersedia']
                ], 404);
            }
            return ApiResponse::success(new ProductsImageResource($productTags), 'Success ambil Product Image detail', 200);
        }


public function destroy($id)
{
    try {
        $image = $this->ProductsImageModel->findOrFail($id);
        $imageprod = $this->ProductsImageModel
                          ->select('product_images.*', 'products.name as name_product')
                        ->leftJoin('products', 'products.id', '=', 'product_images.product_id')
                        ->where('product_images.id', $id)
                        ->first();

        // Cegah hapus kalau itu default.jpeg
        if ($image->image_url !== 'default.jfif' && Storage::disk('public')->exists('image/products/' . $image->image_url)) {
            Storage::disk('public')->delete('image/products/' . $image->image_url);
        }

        $image->delete();

          return ApiResponse::success(new ProductsImageResource($imageprod), 'Images Product berhasil dihapus.', 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return ApiResponse::error('Gambar Dengan ID Tersebut tidak ditemukan', [], 404);
    } catch (\Exception $e) {
        return ApiResponse::error('Terjadi kesalahan saat menghapus gambar', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

}
