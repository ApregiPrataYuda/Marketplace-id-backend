<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\Products;
use App\Http\Controllers\Api\Admin\Category;
use App\Http\Controllers\Api\Admin\Variant;
use App\Http\Controllers\Api\Admin\Tags;
use App\Http\Controllers\Api\Admin\ProductTags;
use App\Http\Controllers\Api\Admin\ProductsImage;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// this is route for products
Route::get('products', [Products::class, 'index'])->name('api.products');
Route::post('products-create', [Products::class, 'store'])->name('api.add.products');         
Route::get('products-select', [Products::class, 'select'])->name('api.select.products');         
Route::get('products-detail/{id}', [Products::class, 'show'])->name('api.show.products');
Route::put('products-update/{id}', [Products::class, 'update'])->name('api.update.products');             
Route::delete('products-delete/{id}', [Products::class, 'destroy'])->name('api.delete.products');   



// this is route for Category
Route::get('category', [Category::class, 'index'])->name('api.category');
Route::get('category-detail/{id}', [Category::class, 'show'])->name('api.show.category');
Route::post('category-create', [Category::class, 'store'])->name('api.add.category');         
Route::put('category-update/{id}', [Category::class, 'update'])->name('api.update.category');  
Route::delete('category-delete/{id}', [Category::class, 'destroy'])->name('api.delete.category');



Route::get('variant', [Variant::class, 'index'])->name('api.variant');
Route::get('variant-detail/{id}', [Variant::class, 'show'])->name('api.show.variant');
Route::post('variant-create', [Variant::class, 'store'])->name('api.add.variant');     
Route::put('variant-update/{id}', [Variant::class, 'update'])->name('api.update.variant');  
Route::delete('variant-delete/{id}', [Variant::class, 'destroy'])->name('api.delete.variant');



Route::get('tags', [Tags::class, 'index'])->name('api.tags');
Route::get('tags-select', [Tags::class, 'select'])->name('api.select.tags');
Route::get('tags-detail/{id}', [Tags::class, 'show'])->name('api.show.tags');
Route::post('tags-create', [Tags::class, 'store'])->name('api.add.tags');  
Route::put('tags-update/{id}', [Tags::class, 'update'])->name('api.update.tags');  
Route::delete('tags-delete/{id}', [Tags::class, 'destroy'])->name('api.delete.tags');



Route::get('tags-product', [ProductTags::class, 'index'])->name('api.tags.product');
Route::get('tags-product-detail/{id}', [ProductTags::class, 'show'])->name('api.show.tags.product');
Route::post('tags-product-create', [ProductTags::class, 'store'])->name('api.add.tags.product');  
Route::put('tags-product-update/{id}', [ProductTags::class, 'update'])->name('api.update.tags.product');  
Route::delete('tags-product-delete/{id}', [ProductTags::class, 'destroy'])->name('api.delete.tags.product');


Route::get('images-product', [ProductsImage::class, 'index'])->name('api.images.product');

Route::post('images-product-create', [ProductsImage::class, 'store'])->name('api.add.images.product'); 
// ini untuk testing di postman
Route::post('images-product-update/{id}', [ProductsImage::class, 'update'])->name('api.update.images.product');  
// ini untuk di production atau mau di gunakan
// Route::put('images-product-update/{id}', [ProductsImage::class, 'update'])->name('api.update.images.product');  
Route::get('images-product-detail/{id}', [ProductsImage::class, 'show'])->name('api.show.images.product');
Route::delete('images-product-delete/{id}', [ProductsImage::class, 'destroy'])->name('api.delete.images.product');
Route::get('images-by-product/{id}', [ProductsImage::class, 'getImagesByProduct'])->name('api.images.by.product');