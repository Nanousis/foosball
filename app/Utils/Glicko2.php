<?php

namespace App\Utils;

use App\Models\Games;
use App\Models\Players;
use App\Models\RatingProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

const TAU = 0.2;
const EPSILON = 0.000001;

const STARTING_RATING = 1500;
const STARTING_RD = 350;
const STARTING_VOLATILITY = 0.08;

class Glicko2 {
    public static function updateElo($player, $otherTeammate, $opponents, $playerScore, $opponentScore) {
        $today = Carbon::today();
        $currentDay = self::getCurrentRatingDay();
        while ($currentDay->lt($today)) {
            echo "Processing rating period: " . $currentDay->toDateString() . "\n";

            self::updateAllPlayersForDay($currentDay);

            $currentDay = self::advanceCurrentRatingDay($currentDay);
        }

        // self::updatePlayerRatingPeriod($player);
    }

    public static function getCurrentRatingDay(): Carbon {
        $record = RatingProgress::first();

        if (!$record) {
            return Carbon::create(2000, 1, 1);
        }

        return Carbon::parse($record->current_rating_day);
    }

    public static function advanceCurrentRatingDay(Carbon $day): Carbon {
        $record = RatingProgress::first();

        if (!$record) {
            $record = RatingProgress::create([
                'current_rating_day' => Carbon::create(2000, 1, 1)->toDateString(),
            ]);
        }

        $nextDay = $day->addDay();
        $record->current_rating_day = $nextDay->toDateString();
        // $record->save();

        return $nextDay;
    }

      public static function updateAllPlayersForDay(Carbon $day) {
        $players = Players::all();

        foreach ($players as $player) {
            $games = Games::whereDate('created_at', $day)
                ->where(function ($query) use ($player) {
                    $query->where('winner1_id', $player->id)
                          ->orWhere('winner2_id', $player->id)
                          ->orWhere('loser1_id', $player->id)
                          ->orWhere('loser2_id', $player->id);
                })
                ->get();

            self::performRatingPeriodUpdate($player, $games, $day);

            // $player->last_rating_update_at = $day->copy()->endOfDay();
            // $player->save();
        }
    }

    static function performRatingPeriodUpdate($player, $games, $day) {
        $n = count($games);
        echo "Player $player->name played $n games on day $day\n";
    }


}
