<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\DataSubmissionController;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/',  [DataSubmissionController::class,'create'])->name('input.create');
Route::post('/input', [DataSubmissionController::class,'store'])->name('input.store');

Route::prefix('api')->group(function () {
  Route::get('/ppks',       [LookupController::class,'ppks']);              // ?satker_id
  Route::get('/packages',   [LookupController::class,'packages']);          // ?satker_id&ppk_id
  Route::get('/job-cats',   [LookupController::class,'jobCategories']);     // selalu 3
  Route::get('/items',      [LookupController::class,'items']);             // ?package_id&job_category_id
  Route::get('/package/{package}', [LookupController::class,'packageShow']); // lokasi
});

