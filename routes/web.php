<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodolistController;

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

Route::group(['prefix' => 'todo-list', 'as' => 'todo-list.'], function(){
    Route::controller(TodolistController::class)->group(function(){
        Route::get('/', 'index')->name('todoList');
        Route::post('/store', 'store')->name('store');
        Route::delete('/delete/{id}', 'destroy')->name('delete');
        Route::post('/update-status/{id}', 'updateStatus')->name('updateStatus');
        Route::post('/mark-all/{status?}', 'markAll')->name('markAll');
    });
});
