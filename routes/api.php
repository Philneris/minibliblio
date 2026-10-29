<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\MemberController;
use Illuminate\Support\Facades\Route;


// Livres
Route::apiResource('books', BookController::class);

// Membres
Route::apiResource('members', MemberController::class);

// Emprunts
Route::get('loans', [LoanController::class, 'index']);
Route::post('loans', [LoanController::class, 'store']);
Route::patch('loans/{loan}/return', [LoanController::class, 'update']);
