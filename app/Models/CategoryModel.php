<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryModel extends Model
{
     use HasFactory,
         SoftDeletes;
    protected $table = 'categories';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'name',
        'slug',
        'description',
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
            $q->where('name', 'like', "%{$search}%");
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
        $errors['name'] = ['Nama Category sudah digunakan.'];
    }

    return $errors;
}
}
