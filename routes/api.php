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
Route::get('/users',[RoleController::class,'getUsers'])->middleware(Auth::class);
Route::put('/change-role',[UserController::class,'changeRole'])->middleware(Role::class);
Route::post('/refresh',[UserController::class,'refresh'])->middleware(Auth::class);
Route::delete('/user/{id}',[UserController::class,'deleteUser'])->middleware(Role::class);
Route::post('/user',[UserController::class,'addUser'])->middleware(Role::class);


Route::post('/add-product',[ProductController::class,'addProduct'])->middleware(Auth::class);
Route::get('/products',[ProductController::class, 'getAll']);
Route::get('/product/{id}',[ProductController::class,'getProductById']);
Route::delete('/product/{id}',[ProductController::class,'deleteProduct'])->middleware(Role::class);

Route::get('/categories',[ProductCategoryController::class,'getAll']);
Route::post('add-category',[ProductCategoryController::class,'addCategory'])->middleware(Role::class);
