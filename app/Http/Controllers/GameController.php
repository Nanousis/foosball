<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Games;
use App\Models\Players;

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

        Games::create([
            'winner1_id' => $winners[0],
            'winner2_id' => $winners[1],
            'loser1_id' => $losers[0],
            'loser2_id' => $losers[1],
            'winner_score' => $winner_score,
            'loser_score' => $loser_score,
        ]);
        foreach ($winners as $winnerId) {
            $player = Players::find($winnerId);
            if ($player) {
                $player->increment('wins');
                $player->elo += 50;
                $player->save();
            }
        }
        
        // Update loss count for losers
        foreach ($losers as $loserId) {
            $player = Players::find($loserId);
            if ($player) {
                $player->increment('losses');
                $player->elo -= 50;
                $player->save();
            }
        }
        return redirect()->back()->with('success', 'Game recorded successfully!');
    }
}

