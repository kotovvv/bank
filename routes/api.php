<?php

use App\Http\Controllers\API\v1\loginController;
use App\Http\Controllers\API\v1\importController;
use App\Http\Controllers\API\v1\UsersController;
use App\Http\Controllers\API\v1\ClientsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::resource('users', UsersController::class);

Route::post('/login',[loginController::class,'login']);
Route::post('import',[importController::class,'store']);
Route::get('export',[importController::class,'export']);

Route::get('getusers', [UsersController::class,'getusers'])->name('getusers');
Route::post('getusers', [UsersController::class,'getrelatedusers'])->name('getrelatedusers');
Route::get('users/getroles', [UsersController::class,'getroless'])->name('user.getroles');
Route::post('user/update', [UsersController::class,'update'])->name('user.update');
Route::delete('user/{id}', [UsersController::class,'deleteuser']);

Route::post('getClients', [ClientsController::class,'getClients']);
Route::get('one', [ClientsController::class,'one']);

