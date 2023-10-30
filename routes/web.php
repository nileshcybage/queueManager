<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShipmentProgressController;
use App\Http\Controllers\ShipperController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



Route::get('/tracking/{shipper}/{trackingnumber}', [ShipmentProgressController::class, 'getTracking'])->middleware(['auth'])->name('gettracking');

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {

    Route::group(['middleware' => 'auth'], function () {


        Route::get('/shippers', [ShipperController::class, 'index'])->name('shippers');
        Route::get('/create-shipper', [ShipperController::class, 'create'])->name('create-shipper');
        Route::post('/store-shipper', [ShipperController::class, 'store'])->name('store-shipper');

        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
        Route::get('/delete/{tablename}/{id}', [HomeController::class, 'delete'])->name('delete-entry');
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::get('/shipment-progress', [ShipmentProgressController::class, 'index'])->name('shipment-progress');
    });
});



require __DIR__ . '/auth.php';








