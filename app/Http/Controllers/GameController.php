<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Games;
use App\Models\Players;
use App\Utils\Glicko2;
use Carbon\Carbon;

class GameController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team1' => 'required|array|size:2',
            'team1.*' => 'required|exists:players,id|distinct',
            'team2' => 'required|array|size:2',
            'team2.*' => 'required|exists:players,id|distinct|different:team1.0|different:team1.1',
            'team1_score' => 'required|integer',
            'team2_score' => 'required|integer',
            'password' => 'required|string|min:2',
        ]);
        if( $validated['password'] !== env('APP_PASSWORD')) {
            return back()->withErrors(['error' => 'Invalid password.']);
        }
        if ($validated['team1_score'] > $validated['team2_score']) {
            $winners = $validated['team1'];
            $losers = $validated['team2'];
            $winner_score = $validated['team1_score'];
            $loser_score = $validated['team2_score'];
        } elseif ($validated['team1_score'] < $validated['team2_score']) {
            $winners = $validated['team2'];
            $losers = $validated['team1'];
            $winner_score = $validated['team2_score'];
            $loser_score = $validated['team1_score'];
        } else {
            return back()->withErrors(['error' => 'Draws are not allowed.']);
        }
        $winner_players = [
            Players::find($winners[0]),
            Players::find($winners[1])
        ];

        $loser_players = [
            Players::find($losers[0]),
            Players::find($losers[1])
        ];

        $winner1_new_rating = Glicko2::updateRating($winner_players[0], Carbon::today());
        $winner2_new_rating = Glicko2::updateRating($winner_players[1], Carbon::today());
        $loser1_new_rating = Glicko2::updateRating($loser_players[0], Carbon::today());
        $loser2_new_rating = Glicko2::updateRating($loser_players[1], Carbon::today());

        $winner1_elo_change = $winner1_new_rating - $winner_players[0]->last_displayed_rating;
        $winner2_elo_change = $winner2_new_rating - $winner_players[1]->last_displayed_rating;
        $loser1_elo_change = $loser1_new_rating - $loser_players[0]->last_displayed_rating;
        $loser2_elo_change = $loser2_new_rating - $loser_players[1]->last_displayed_rating;

        $winner_elo = [
            $winner1_new_rating,
            $winner2_new_rating,
        ];

        $loser_elo = [
            $loser1_new_rating,
            $loser2_new_rating,
        ];


        Games::create([
            'winner1_id' => $winners[0],
            'winner2_id' => $winners[1],
            'loser1_id' => $losers[0],
            'loser2_id' => $losers[1],
            'winner_score' => $winner_score,
            'loser_score' => $loser_score,
            'winner1_elo_change' => round($winner1_elo_change),
            'winner2_elo_change' => round($winner2_elo_change),
            'loser1_elo_change'  => round($loser1_elo_change),
            'loser2_elo_change'  => round($loser2_elo_change),
        ]);



        foreach ($winner_players as $i => $player) {
            if ($player) {
                $player->increment('wins');
                $player->elo = $winner_elo[$i];
                $player->last_displayed_rating = $winner_elo[$i];
                $player->total_score += $winner_score;
                $player->games_played += 1;
                $player->save();
            }
        }

        // Update loss count for losers
        foreach ($loser_players as $i => $player) {
            if ($player) {
                $player->increment('losses');
                $player->elo = $loser_elo[$i];
                $player->last_displayed_rating = $loser_elo[$i];
                $player->total_score += $loser_score;
                $player->games_played += 1;
                $player->save();
            }
        }
        return redirect()->back()->with('success', 'Game recorded successfully!');
    }
}

