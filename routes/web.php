<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameController;
use App\Models\Players;

Route::get('/', function () {
    $players = Players::orderBy('wins', 'desc')->get();
    return view('welcome',['players' => $players]);
})->name('home');
// User Management
Route::get('/user_mng', function () {
    $players = Players::orderBy('wins', 'desc')->get();
    return view('user_mng', ['players' => $players]);
})->name('players.register');

Route::post('/register_user', [UserController::class, 'registerUser'])->name('players.register_user');
Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->name('players.delete');

// Games
Route::get('/record_game', function () {
    $players = Players::all();
    return view('record_game', ['players' => $players]);
})->name('games.store');
Route::post('/record_game', [GameController::class, 'store'])->name('games.store');
