<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Games;
use App\Models\Players;

function playerKValue($player) {
    $games = $player->wins + $player->losses;

    return 16 + 14 / (1 + exp($games - 18));
}

function expectedRating($player, $opponents) {
    $e1 = 1/(1 + 10**(($opponents[0]->elo - $player->elo)/500));
    $e2 = 1/(1 + 10**(($opponents[1]->elo - $player->elo)/500));

    return ($e1 + $e2) / 2;
}

function expectedTeamRating($players, $opponents) {
    return (expectedRating($players[0], $opponents) + expectedRating($players[1], $opponents)) / 2;
}

function eloChange($players, $opponents, $i, $did_win) {
    $player_rating = expectedRating($players[$i], $opponents);
    $k = playerKValue($players[$i]);


    $actual_score = 0;
    if ($did_win) {
        $actual_score = 1;
    }

    return $k * ($actual_score - $player_rating);
}

class GameController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team1' => 'required|array|size:2',
            'team1.*' => 'required|exists:players,id|distinct',
            'team2' => 'required|array|size:2',
            'team2.*' => 'required|exists:players,id|distinct|different:team1.0|different:team1.1',
            'team1_score' => 'required|integer|min:0',
            'team2_score' => 'required|integer|min:0',
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

        $winner1_elo_change = eloChange($winner_players, $loser_players, 0, true);
        $winner2_elo_change = eloChange($winner_players, $loser_players, 1, true);
        $loser1_elo_change  = eloChange($loser_players, $winner_players, 0, false);
        $loser2_elo_change  = eloChange($loser_players, $winner_players, 1, false);

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
                $elo_change =  eloChange($winner_players, $loser_players, $i, true);;
                $player->elo += $elo_change;
                $player->total_score += $winner_score;
                $player->games_played += 1;
                $player->save();
            }
        }

        // Update loss count for losers
        foreach ($loser_players as $i => $player) {
            if ($player) {
                $player->increment('losses');
                $elo_change = eloChange($loser_players, $winner_players, $i, false);
                $player->elo += $elo_change;
                $player->total_score += $loser_score;
                $player->games_played += 1;
                $player->save();
            }
        }
        return redirect()->back()->with('success', 'Game recorded successfully!');
    }
}

