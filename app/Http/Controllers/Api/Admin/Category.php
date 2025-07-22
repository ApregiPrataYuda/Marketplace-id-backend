<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\ValidationIndexCategoryRequest;
use App\Http\Resources\CategoryResourceCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\ValidationAddCategoryRequest;
use App\Models\CategoryModel;


         // Contoh URL penggunaan:
        // ?search=Kilogram              -> cari berdasarkan menu
        // ?per_page=20                 -> jumlah data per halaman
        // ?search=admin&per_page=10    -> pencarian + pagination
        // ?sort_by=menu&sort_dir=asc   -> sorting berdasarkan kolom
        // ?only_deleted=true           -> hanya tampilkan soft deleted

class Category extends Controller
{
    protected $CategoryModel;
    public function __construct(CategoryModel $CategoryModel) {
        $this->CategoryModel = $CategoryModel;
    }

      public function index(ValidationIndexCategoryRequest $request) {
         $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $perPage = is_numeric($validated['per_page'] ?? null) ? $validated['per_page'] : 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $onlyDeleted = $validated['only_deleted'] ?? false;

        $query = $this->CategoryModel
            ->onlyDeleted($onlyDeleted)
            ->search($search)
            ->sort($sortBy, $sortDir);
        $results = $query->paginate($perPage);
        $message = $results->isEmpty() ? "Data yang Anda cari tidak ditemukan" : "Success";
        return ApiResponse::paginate(new CategoryResourceCollection($results), $message);
      }


        public function show(string $id)
        {
            $Category = $this->CategoryModel->find($id);
            if (!$Category) {
                return ApiResponse::error('Category not found', [
                    'id' => ['Data dengan ID tersebut tidak tersedia']
                ], 404);
            }
            return ApiResponse::success(new CategoryResource($Category), 'Success ambil category detail', 200);
        }



public function store(ValidationAddCategoryRequest $request)
{
    $data = $request->validated();

    try {
        // Optional: cek duplikasi manual (jika memang ingin override validasi laravel)
        $errors = CategoryModel::isDuplicate($data); 
         if (!empty($errors)) {
                 return ApiResponse::error('Validasi gagal', $errors, 400);
            }

        $Category = $this->CategoryModel->create([
            'name'     => $data['name'],
            'slug'   => Str::slug($data['name']), 
            'description'    => $data['description'],
        ]);

        return ApiResponse::success(new CategoryResource($Category), 'Success Create New Category', 201);

    } catch (\Illuminate\Database\QueryException $e) {
        return ApiResponse::error('Gagal membuat category (query error)', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 422);
    } catch (\Exception $e) {
        return ApiResponse::error('Terjadi kesalahan saat membuat category', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}



public function update(ValidationAddCategoryRequest $request, $id)
{
    $data = $request->validated();

    $category = $this->CategoryModel->find($id);

    if (!$category) {
         return ApiResponse::error('Category dengan ID tersebut tidak ditemukan.', [
                'id' => ['Data tidak tersedia.']
            ], 404);
    }

    try {
         // Validasi duplikat
        $errors = CategoryModel::isDuplicate($data, $id);
                if (!empty($errors)) {
                 return ApiResponse::error('Validasi gagal', $errors, 400);
            }

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return ApiResponse::success(new CategoryResource($category), 'Success Update category', 200);
    } catch (\Illuminate\Database\QueryException $e) {
        return ApiResponse::error('Gagal update category (query error)', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 422);
    } catch (\Exception $e) {
        return ApiResponse::error('Failed to update category', [
            'exception' => config('app.debug') ? $e->getMessage() : 'Please try again later'
        ], 500);
    }
}


public function destroy(string $id)
{
    try {
        $category = $this->CategoryModel->find($id);

       if (!$category) {
            return ApiResponse::error('Kategori dengan ID tersebut tidak ditemukan.', [
                'id' => ['Data tidak tersedia.']
            ], 404);
        }

        $category->delete();

        return ApiResponse::success(new CategoryResource($category), 'Success Delete category', 200);
    } catch (\Exception $e) {
        return ApiResponse::error('Failed to delete category', [
            'exception' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

}
