<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;


class VariantModel extends Model
{
      use HasFactory,
          SoftDeletes;
    protected $table = 'product_variants';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'product_id',
        'name',
    ];


      public function scopeOnlyDeleted(Builder $query, bool $only = false): Builder
    {
        return $only ? $query->onlyTrashed() : $query;
    }



public function scopeSearch($query, $search)
{
    if ($search) {
        return $query->where(function ($q) use ($search) {
            $q->where('product_variants.name', 'like', "%{$search}%");
        });
    }
    return $query;
}



// Scope untuk sorting dinamis
// public function scopeSort($query, $sortBy, $sortDir)
// {
//     return $query->orderBy($sortBy ?? 'product_variants.created_at', $sortDir ?? 'asc');
// }


public function scopeSort($query, $sortBy, $sortDir)
{
    $allowedSort = [
        'product_variants.name',
        'product_variants.created_at',
        'products.name'
    ];

    if (!in_array($sortBy, $allowedSort)) {
        $sortBy = 'product_variants.created_at';
    }

    $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

    return $query->orderBy($sortBy, $sortDir);
}




public static function isDuplicate(array $data, $id = null): array
{
    $errors = [];

    $query = static::where('product_variants.name', $data['name']);

    if ($id) {
        $query->where('product_variants.id', '!=', $id); // Kecualikan ID yang sedang diupdate
    }

    if ($query->exists()) {
        $errors['name'] = ['Nama Variant sudah digunakan.'];
    }

    return $errors;
}
}
