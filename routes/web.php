<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameController;
use App\Models\Players;
use App\Models\Games;
use App\Utils\Glicko2;
use Illuminate\Http\Request;


Route::post('/api/preview-game', function (Request $request) {
    $team1 = $request->input('team1'); // array of 2 player IDs
    $team2 = $request->input('team2');

    $t1 = [Players::find($team1[0]), Players::find($team1[1])];
    $t2 = [Players::find($team2[0]), Players::find($team2[1])];

    $odds = Glicko2::getGameOdds($t1, $t2);

    if ($odds[0] == 1) {
        $winner = 'Team 1';
        $loser = 'Team 2';
    } else {
        $winner = 'Team 2';
        $loser = 'Team 1';
    }


    $score = intval(floor(10 * (1-$odds[1])/($odds[1])));

    return response()->json([
        'winner' => $winner,
        'loser' => $loser,
        'min_score' => $score,
    ]);
})->name('api.preview-game');

Route::get('/', function () {
    $players = Players::orderBy('elo', 'desc')->get();
    $games = Games::with(['winner1', 'winner2', 'loser1', 'loser2'])->latest()->get();
    return view('welcome',['players' => $players, 'games' => $games]);
})->name('home');
// User Management
Route::get('/user_mng', function () {
    $players = Players::orderBy('elo', 'desc')->get();
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

Route::get('/games/all', function () {
    $games = Games::with(['winner1', 'winner2', 'loser1', 'loser2'])
        ->latest()
        ->get();

    return response()->json($games);
});

Route::get("/users/{id}/games", function($id) {
    $player = Players::find($id);

    $games = Games::with(['winner1', 'winner2', 'loser1', 'loser2'])
        ->where(function ($query) use ($id) {
            $query->where('winner1_id', $id)
                  ->orWhere('winner2_id', $id)
                  ->orWhere('loser1_id', $id)
                  ->orWhere('loser2_id', $id);
        })
        ->latest()
        ->get();

    foreach ($games as $game) {
        if ($game->winner1_id == $id || $game->winner2_id == $id) {
            $game->result = 'won';
        } else {
            $game->result = 'lost';
        }
    }

    return view('user_games', ['player' => $player, 'games' => $games]);
})->name("players.games");
