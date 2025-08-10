<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductTagsModel extends Model
{
    use HasFactory,
          SoftDeletes;
    protected $table = 'product_tag';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'product_id',
        'tag_id',
    ];

    

    //opsional
    public function scopeOnlyDeleted(Builder $query, bool $only = false): Builder
    {
        return $only ? $query->onlyTrashed() : $query;
    }



// public function scopeSearch($query, $search)
// {
//     if ($search) {
//         return $query->where(function ($q) use ($search) {
//             $q->where('products.name', 'like', "%{$search}%");
//         });
//     }
//     return $query;
// }

public function scopeSearch($query, $search)
{
    if ($search) {
        return $query->where(function ($q) use ($search) {
            $q->where('products.name', 'like', "%{$search}%")
              ->orWhere('tags.name', 'like', "%{$search}%");
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

    $query = static::where('product_id', $data['product_id'])
                   ->where('tag_id', $data['tag_id']);

    if ($id) {
        $query->where('id', '!=', $id); // Kecualikan ID yang sedang diupdate
    }

    if ($query->exists()) {
        $errors['product_tag'] = ['Kombinasi produk dan tag sudah ada.'];
    }

    return $errors;
}

}
