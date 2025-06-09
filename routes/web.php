<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TaskController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [CalendarController::class, 'index'])->name('calendar.index');
Route::get('/calendar', [CalendarController::class, 'show'])->name('calendar.show');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
