<?php

use App\Http\Controllers\API\v1\loginController;
use App\Http\Controllers\API\v1\importController;
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

Route::post('/login',[loginController::class,'login']);
Route::post('import',[importController::class,'store']);
Route::get('export',[importController::class,'export']);
