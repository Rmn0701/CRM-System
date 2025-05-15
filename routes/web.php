<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;

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


Route::resource('contacts', ContactController::class);
Route::post('contacts/filter', [ContactController::class, 'filter'])->name('contacts.filter');
Route::get('/contacts/available-for-merge/{id}', [ContactController::class, 'availableForMerge']);
Route::post('contacts/merge', [ContactController::class, 'merge'])->name('contacts.merge');


Route::get('contact-fields/list', [CustomFieldController::class, 'list'])->name('contact-fields-list');
Route::resource('contact-fields', CustomFieldController::class);


Route::get('/contacts/ajax/create', function () {
    return view('contacts.form');
})->name('contacts.ajax.create');
