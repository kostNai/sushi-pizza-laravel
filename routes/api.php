<?php

use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\Auth;
use App\Http\Middleware\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/register',[UserController::class,'register']);
Route::post('login',[UserController::class,'login']);
Route::post('/logout',[UserController::class,'logOut'])->middleware(Auth::class);
Route::put('/change-role',[UserController::class,'changeRole'])->middleware(Role::class);
Route::post('/refresh',[UserController::class,'refresh'])->middleware(Auth::class);
Route::delete('/user/{id}',[UserController::class,'deleteUser'])->middleware(Role::class);
Route::post('/user',[UserController::class,'addUser'])->middleware(Role::class);
Route::post('/user/{id}',[UserController::class,'editUser'])->middleware(Role::class);
Route::patch('/user',[UserController::class,'editCurrentUser'])->middleware(Role::class);
Route::get('/users',[UserController::class,'getAll'])->middleware(Role::class);
Route::get('/user',[UserController::class,'getCurrentUser'])->middleware(Auth::class);



Route::post('/add-product',[ProductController::class,'addProduct'])->middleware(Auth::class);
Route::get('/paginate-products/',[ProductController::class, 'getAllWithPaginate']);
Route::get('/products/',[ProductController::class, 'getAll']);
Route::get('/product/{id}',[ProductController::class,'getProductById']);
Route::delete('/product/{id}',[ProductController::class,'deleteProduct'])->middleware(Role::class);
Route::post('/product/{id}',[ProductController::class,'addImage'])->middleware(Role::class);
Route::put('/product/{id}',[ProductController::class,'editProduct'])->middleware(Role::class);
Route::get('/products-by-category',[ProductController::class,'getByCategory']);
Route::get('/get-ordered-product',[ProductController::class,'getOrderedProduct']);

Route::get('/categories',[ProductCategoryController::class,'getAll']);
Route::post('/add-category',[ProductCategoryController::class,'addCategory'])->middleware(Role::class);


Route::get('/roles',[RoleController::class,'getAll'])->middleware(Role::class);
Route::post('/role',[RoleController::class,'addRole'])->middleware(Role::class);
