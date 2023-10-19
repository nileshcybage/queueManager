<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShipmentProgressController;
use App\Http\Controllers\ShipperController;
use App\Http\Controllers\UserController;


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



        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');


        Route::get('/shippers', [ShipperController::class, 'index'])->name('shippers');
        Route::get('/users', [UserController::class, 'index'])->name('users');
    });
});



require __DIR__ . '/auth.php';








