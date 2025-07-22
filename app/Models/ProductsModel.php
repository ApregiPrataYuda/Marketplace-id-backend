<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsModel extends Model
{
      use HasFactory,
          SoftDeletes;
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'name',
        'category_id',
        'slug',
        'description',
        'price',
        'stock',
        'status',
    ];
    


    //opsional
    public function scopeOnlyDeleted(Builder $query, bool $only = false): Builder
    {
        return $only ? $query->onlyTrashed() : $query;
    }



public function scopeSearch($query, $search)
{
    if ($search) {
        return $query->where(function ($q) use ($search) {
            $q->where('products.name', 'like', "%{$search}%")
              ->orWhere('products.status', 'like', "%{$search}%");
        });
    }
    return $query;
}



// Scope untuk sorting dinamis
public function scopeSort($query, $sortBy, $sortDir)
{
    return $query->orderBy($sortBy ?? 'created_at', $sortDir ?? 'asc');
}

public static function isDuplicate(array $data, $id = null): array
{
    $errors = [];

    $query = static::where('name', $data['name']);

    if ($id) {
        $query->where('id', '!=', $id); // Kecualikan ID yang sedang diupdate
    }

    if ($query->exists()) {
        $errors['name'] = ['Nama Product sudah digunakan.'];
    }

    return $errors;
}

// public static function isDuplicate(array $data): array
// {
//     $errors = [];

//     if (static::where('name', $data['name'])->exists()) {
//         $errors['name'] = ['Nama Product sudah digunakan.'];
//     }

//     return $errors;
// }

}
