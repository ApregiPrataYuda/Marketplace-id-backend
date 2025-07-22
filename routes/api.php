<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\Products;
use App\Http\Controllers\Api\Admin\Category;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// this is route for products
Route::get('products', [Products::class, 'index'])->name('api.products');
Route::post('products-create', [Products::class, 'store'])->name('api.add.products');         
Route::get('products-detail/{id}', [Products::class, 'show'])->name('api.show.products');
Route::put('products-update/{id}', [Products::class, 'update'])->name('api.update.products');             
Route::delete('products-delete/{id}', [Products::class, 'destroy'])->name('api.delete.products');   



// this is route for Category
Route::get('category', [Category::class, 'index'])->name('api.category');
Route::get('category-detail/{id}', [Category::class, 'show'])->name('api.show.category');
Route::post('category-create', [Category::class, 'store'])->name('api.add.category');         
Route::put('category-update/{id}', [Category::class, 'update'])->name('api.update.category');  
Route::delete('category-delete/{id}', [Category::class, 'destroy'])->name('api.delete.category');