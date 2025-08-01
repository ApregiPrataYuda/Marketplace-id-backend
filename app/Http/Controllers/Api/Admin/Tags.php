<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use App\Http\Requests\ValidationIndexTags;
use App\Http\Resources\TagsResource;
use App\Http\Resources\TagsCollectionResource;
use App\Http\Requests\ValidationAddTagsRequest;
use App\Models\TagsModel;


  // Contoh URL penggunaan:
        // ?search=Kilogram              -> cari berdasarkan menu
        // ?per_page=20                 -> jumlah data per halaman
        // ?search=admin&per_page=10    -> pencarian + pagination
        // ?sort_by=menu&sort_dir=asc   -> sorting berdasarkan kolom
        // ?only_deleted=true           -> hanya tampilkan soft deleted


class Tags extends Controller
{
     protected $TagsModel;
     public function __construct(TagsModel $TagsModel) {
      $this->TagsModel = $TagsModel;
    }

    public function index(ValidationIndexTags $request)  {
         $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $perPage = is_numeric($validated['per_page'] ?? null) ? $validated['per_page'] : 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDir = $validated['sort_dir'] ?? 'desc';
        $onlyDeleted = $validated['only_deleted'] ?? false;

        $query = $this->TagsModel
            ->onlyDeleted($onlyDeleted)
            ->search($search)
            ->sort($sortBy, $sortDir);
        $results = $query->paginate($perPage);
        $message = $results->isEmpty() ? "Data yang Anda cari tidak ditemukan" : "Success";
        return ApiResponse::paginate(new TagsCollectionResource($results), $message);
    }

    
        public function show(string $id)
        {
            $tags = $this->TagsModel->find($id);
            if (!$tags) {
                return ApiResponse::error('tags not found', [
                    'id' => ['Data dengan ID tersebut tidak tersedia']
                ], 404);
            }
            return ApiResponse::success(new TagsResource($tags), 'Success ambil tags detail', 200);
        }


        public function store(ValidationAddTagsRequest $request)
            {
                $data = $request->validated();

                try {
                    // Optional: cek duplikasi manual (jika memang ingin override validasi laravel)
                    $errors = TagsModel::isDuplicate($data); 
                    if (!empty($errors)) {
                            return ApiResponse::error('Validasi gagal', $errors, 400);
                        }

                    $tags = $this->TagsModel->create([
                        'name'     => $data['name'],
                        'slug'   => Str::slug($data['name']), 
                    ]);

                    return ApiResponse::success(new TagsResource($tags), 'Success Create New Tags', 201);

                } catch (\Illuminate\Database\QueryException $e) {
                    return ApiResponse::error('Gagal membuat Tags (query error)', [
                        'exception' => config('app.debug') ? $e->getMessage() : null
                    ], 422);
                } catch (\Exception $e) {
                    return ApiResponse::error('Terjadi kesalahan saat membuat Tags', [
                        'exception' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
            }


            public function update(ValidationAddTagsRequest $request, $id)

                {
                    $data = $request->validated();

                    $tags = $this->TagsModel->find($id);

                    if (!$tags) {
                        return ApiResponse::error('tags dengan ID tersebut tidak ditemukan.', [
                                'id' => ['Data tidak tersedia.']
                            ], 404);
                    }

                    try {
                        // Validasi duplikat
                        $errors = TagsModel::isDuplicate($data, $id);
                                if (!empty($errors)) {
                                return ApiResponse::error('Validasi gagal', $errors, 400);
                            }

                        if (isset($data['name'])) {
                            $data['slug'] = Str::slug($data['name']);
                        }

                        $tags->update($data);

                        return ApiResponse::success(new TagsResource($tags), 'Success Update tags', 200);
                    } catch (\Illuminate\Database\QueryException $e) {
                        return ApiResponse::error('Gagal update tags (query error)', [
                            'exception' => config('app.debug') ? $e->getMessage() : null
                        ], 422);
                    } catch (\Exception $e) {
                        return ApiResponse::error('Failed to update tags', [
                            'exception' => config('app.debug') ? $e->getMessage() : 'Please try again later'
                        ], 500);
                    }
                }

                public function destroy(string $id)
                {
                    try {
                        $tags = $this->TagsModel->find($id);

                    if (!$tags) {
                            return ApiResponse::error('Tags dengan ID tersebut tidak ditemukan.', [
                                'id' => ['Data tidak tersedia.']
                            ], 404);
                        }

                        $tags->delete();

                        return ApiResponse::success(new TagsResource($tags), 'Success Delete tags', 200);
                    } catch (\Exception $e) {
                        return ApiResponse::error('Failed to delete tags', [
                            'exception' => config('app.debug') ? $e->getMessage() : null
                        ], 500);
                    }
                }

}
