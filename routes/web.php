<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RolesAndPermissionController;
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
   // return "<a href='".config('app.frontend_url')."'>DWC Ltd Portal</a>";
    //return view('welcome');
$mysqli = new mysqli("localhost","root","","prod_db");

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
} else {

echo "Successfully connect";
}

});

//  Route::get('topRetailerList',[RolesAndPermissionController::class,'topRetailerList']);
// Auth::routes();
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get(
    '/home',
    [RolesAndPermissionController::class,'topRetailerList']
);
