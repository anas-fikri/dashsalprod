<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', 'App\Http\Controllers\AuthController@showLogin')->name('login');
    Route::post('/login', 'App\Http\Controllers\AuthController@login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', 'App\Http\Controllers\AuthController@logout')->name('logout');
    Route::get('/', 'App\Http\Controllers\DashboardController@index')->name('dashboard');
    Route::get('/detail', 'App\Http\Controllers\DashboardController@detail')->name('dashboard.detail');
    Route::get('/import/templates/{type}', 'App\Http\Controllers\PayrollImportController@downloadTemplate')->name('import.template.download');
    Route::get('/export/payroll', 'App\Http\Controllers\DashboardController@exportPayrollExcel')->name('export.payroll.excel');
    Route::get('/export/trends', 'App\Http\Controllers\DashboardController@exportTrendsExcel')->name('export.trends.excel');

    Route::middleware('role:admin')->group(function () {
        Route::get('/import', 'App\Http\Controllers\PayrollImportController@showForm')->name('import.form');
        Route::post('/import/payroll', 'App\Http\Controllers\PayrollImportController@importPayroll')->name('import.payroll');
        Route::post('/import/payroll/save', 'App\Http\Controllers\PayrollImportController@savePayroll')->name('import.save');
        Route::post('/import/operational', 'App\Http\Controllers\OperationalMetricController@store')->name('import.operational');
        Route::get('/history', 'App\Http\Controllers\PayrollImportController@history')->name('import.history');
        Route::delete('/history/{id}', 'App\Http\Controllers\PayrollImportController@deleteHistory')->name('import.history.delete');
    });
});
