<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DailyCheckSheetController;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\BnfController;
use App\Http\Controllers\ProblemListController;
use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('auth.login');
    });
    // Route::get('/home-monitoring', [HomeController::class, 'indexMonitoring'])->name('home-monitoring');
});

Route::get('/home-monitoring', [MonitoringController::class, 'indexMonitoring'])->name('home-monitoring');
Route::get('/link-storage', function () {
    Artisan::call('storage:link');
});

Route::get('/generate-storage', function(){
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    echo 'ok';
 });


Auth::routes();


Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // Route::get('/home-monitoring', [HomeController::class, 'indexMonitoring'])->name('home-monitoring');

    // Daily checksheet : start
    Route::get('/daily-check-sheet', [DailyCheckSheetController::class, 'index'])->name('daily-check-sheet');
    Route::get('/daily-check-sheet/create', [DailyCheckSheetController::class, 'create'])->name('daily-check-sheet.create');
    Route::post('/daily-check-sheet/store', [DailyCheckSheetController::class, 'store'])->name('daily-check-sheet.store');
    Route::get('/daily-check-sheet/detail/{supplier_id}/{production_date}', [DailyCheckSheetController::class, 'detail'])->name('daily-check-sheet.detail');
    Route::get('/daily-check-sheet/detail-data/{supplier_id}/{production_date}', [DailyCheckSheetController::class, 'detailData'])->name('daily-check-sheet.detail-data');
    Route::get('/daily-check-sheet-input', [DailyCheckSheetController::class, 'input'])->name('daily-check-sheet-input');
    // Daily checksheet : end

    Route::resource('roles', RoleController::class);
    Route::resource('navigation', NavigationController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', UserController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('parts', PartController::class);
    Route::resource('bad-news-firsts', BnfController::class);
    Route::post('/bad-news-firsts/close/{id}', [BnfController::class, 'selesaikan'])->name('bad-news-firsts.selesaikan');
    Route::resource('problems', ProblemListController::class);
    // Route::post('/bad-news-firsts/close/{id}', [BnfController::class, 'selesaikan'])->name('bad-news-firsts.selesaikan');
    Route::post('/problem-list/upload-car', [ProblemListController::class, 'uploadCar'])->name('problem-list.upload-car');
    Route::post('/problem-list/upload-a3', [ProblemListController::class, 'uploadA3Report'])->name('problem-list.upload-a3');
    Route::post('/problem-list/close/{id}', [ProblemListController::class, 'selesaikan'])->name('problem-list.selesaikan');
});
