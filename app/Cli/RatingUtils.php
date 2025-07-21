<?php

namespace App\Cli;

use App\Utils\Glicko2;
use App\Models\Players;
use App\Models\Games;
use App\Models\RatingProgress;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

const TAU = 0.2;
const EPSILON = 0.000001;

const STARTING_RATING = 1500;
const STARTING_RD = 350;
const STARTING_VOLATILITY = 0.08;

class RatingUtils {
    public static function recomputeEloThenPrint() {
        echo "Recomputing Elo...\n";
        self::recomputeElo();
        echo "Done!\n";
        $players = Players::orderBy('rating', 'desc')->get();
        foreach ($players as $player) {
            $rd = $player->rd * 2;
            echo "$player->name: $player->rating +- $rd\n";
        }
    }

    static function resetRatings() {
        DB::table('players')->update([
            'elo'                   => 0,
            'rating'                => STARTING_RATING,
            'rd'                    => STARTING_RD,
            'volatility'            => STARTING_VOLATILITY,
            'last_displayed_rating' => STARTING_RATING,
        ]);

        DB::table('games')->update([
            'game_rated' => false,
        ]);

        $record = RatingProgress::first();
        $record->current_rating_day = Carbon::create(2025, 7, 7);
        $record->save();
    }

    static function recomputeElo() {
        self::resetRatings();

        $games = Games::orderBy('created_at')
              ->orderBy('id')
              ->get();

        foreach ($games as $gi => $game) {
            $game->game_rated = true;
            $game->save();

            $players = [
                'winner1' => $game->winner1,
                'winner2' => $game->winner2,
                'loser1'  => $game->loser1,
                'loser2'  => $game->loser2,
            ];

            foreach ($players as $k => $player) {
                $newRating = Glicko2::updateRating($player, $game->created_at);
                $delta = $newRating - $player->last_displayed_rating;
                $player->last_displayed_rating = $newRating;
                $game[$k . "_elo_change"] = $delta;

                $player->save();
            }
            $game->save();
        }

        foreach (Players::all() as $player) {
            $player->elo = intval($player->last_displayed_rating);
            $player->save();
        }
    }
}
