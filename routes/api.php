<?php

use App\Http\Controllers\API\v1\loginController;
use App\Http\Controllers\API\v1\importController;
use App\Http\Controllers\API\v1\UsersController;
use App\Http\Controllers\API\v1\ClientsController;
use App\Http\Controllers\API\v1\FunnelsController;
use App\Http\Controllers\API\v1\BanksController;
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
Route::resource('funnels', FunnelsController::class);
Route::resource('banks', BanksController::class);

Route::post('/login',[loginController::class,'login']);
Route::post('import',[importController::class,'store']);
Route::get('export',[importController::class,'export']);

Route::get('getusers', [UsersController::class,'getusers'])->name('getusers');
Route::post('getusers', [UsersController::class,'getrelatedusers'])->name('getrelatedusers');
Route::get('users/getroles', [UsersController::class,'getroless'])->name('user.getroles');
Route::post('user/update', [UsersController::class,'update'])->name('user.update');
Route::delete('user/{id}', [UsersController::class,'deleteuser']);

Route::post('getClients', [ClientsController::class,'getClients']);
Route::get('getUserClients/{id}/{bank_id?}/{funnel_id?}', [ClientsController::class,'getUserClients']);
Route::post('changeUserOfClients', [ClientsController::class,'changeUserOfClients']);
Route::post('setBankForClients', [ClientsController::class,'setBankForClients']);
Route::get('getClientsWithoutBanks', [ClientsController::class,'getClientsWithoutBanks']);
Route::post('delBankFromClients', [ClientsController::class,'delBankFromClients']);
Route::post('delClients', [ClientsController::class,'delClients']);
Route::post('getReportAll', [ClientsController::class,'getReportAll']);
Route::post('recall', [ClientsController::class,'recall']);
Route::post('canTel', [BanksController::class,'canTel']);
Route::post('updateStatus', [BanksController::class,'updateStatus']);
Route::get('getRegions', [BanksController::class,'getRegions']);
Route::post('getCities', [BanksController::class,'getCities']);
Route::post('getBranches', [BanksController::class,'getBranches']);
Route::post('sendOrder', [BanksController::class,'sendOrder']);


