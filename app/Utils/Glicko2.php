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


function glickoG(float $phi) {

    return 1 / sqrt(1 + 3 * ($phi*$phi) / (pi() * pi()));
}

function glickoE(float $mu, float $mu_j, float $phi_j) {
    $g = glickoG($phi_j);
    return 1 / (1 + exp(-$g * ($mu - $mu_j)));
}

function getMu($player) {
    return ($player->rating - 1500) / 173.7178;
}

function getPhi($player) {
    return $player->rd / 173.7178;
}

function getTeamMu($plr1, $plr2) {
    return (getMu($plr1) + getMu($plr2)) / 2;
}

function getTeamPhi($plr1, $plr2) {
    $phi1 = getPhi($plr1);
    $phi2 = getPhi($plr2);

    return sqrt(($phi1*$phi1 + $phi2*$phi2) / 2);
}

function calculateSigma($sigma, $phi, $v, $delta) {
    $a_fix = log($sigma * $sigma);

    $f = function ($x) use ($sigma, $phi, $delta, $v, $a_fix) {
        $den = ($phi*$phi+$v+exp($x));
        $first = ($delta*$delta - $phi*$phi - $v - exp($x)) / (2 * $den * $den);
        $second = ($x-$a_fix)/(TAU*TAU);

        return $first - $second;
    };

    $a = $a_fix;
    $b = 0.0;
    if ($delta*$delta > $phi*$phi + $v) {
        $b = log($delta*$delta - $phi*$phi - $v);
    } else {
        $k = 1;

        while ($f($a - $k * TAU) < 0) {
            $k += 1;
        }
        $b = $a - $k * TAU;
    }

    $fa = $f($a);
    $fb = $f($b);


    while (($b - $a) > EPSILON) {
        $c = $a + (($a - $b) * $fa) / ($fb - $fa);
        $fc = $f($c);
        if ($fc * $fb <= 0) {
            $a = $b;
            $fa = $fb;
        } else {
            $fa = $fa / 2;
        }

        $b = $c;
        $fb = $fc;
    }

    return exp($a/2);
}

function normalizeScore($plr, $opp) {
    return $plr / ($plr + $opp);
}

class Glicko2 {
    public static function updateRating($player, $day, $game) {
        $currentDay = self::getCurrentRatingDay()->startOfDay();
        $day = $day->startOfDay();

        while ($currentDay->lt($day)) {
            self::updateAllPlayersForDay($currentDay);
            $currentDay = self::advanceCurrentRatingDay($currentDay);
        }

        $gamesPlayedToday = Games::whereDate('created_at', $day)
            ->where('game_rated', true)
            ->where(function ($query) use ($player) {
                $query->where('winner1_id', $player->id)
                  ->orWhere('winner2_id', $player->id)
                  ->orWhere('loser1_id', $player->id)
                  ->orWhere('loser2_id', $player->id);
            })->get();

        $gamesPlayedToday[] = $game;


        $mod = self::performRatingPeriodUpdate($player, $gamesPlayedToday);
        return $mod['rating'];
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
        $record->save();

        return $nextDay;
    }

    public static function getGameOdds($team1, $team2) {
        $mu_1 = getTeamMu($team1[0], $team1[1]);
        $phi_1 = getTeamPhi($team1[0], $team1[1]);

        $mu_2 = getTeamMu($team2[0], $team2[1]);
        $phi_2 = getTeamPhi($team2[0], $team2[1]);

        if ($mu_1 > $mu_2) {
            $e = glickoE($mu_1, $mu_2, $phi_2);
            $e = min(max($e, 0.1), 0.9);
            return [1, $e];
        } else {
            $e = glickoE($mu_2, $mu_1, $phi_1);
            $e = min(max($e, 0.1), 0.9);
            return [2, $e];
        }
    }

    public static function updateAllPlayersForDay(Carbon $day) {
        $players = Players::all();

        $modified_players = [];

        foreach ($players as $player) {
            $games = Games::whereDate('created_at', $day)
                ->where(function ($query) use ($player) {
                    $query->where('winner1_id', $player->id)
                          ->orWhere('winner2_id', $player->id)
                          ->orWhere('loser1_id', $player->id)
                          ->orWhere('loser2_id', $player->id);
                })
                ->get();

            $modified_players[] = self::performRatingPeriodUpdate($player, $games);
        }

        foreach ($modified_players as $mod_plr) {
            $player = Players::find($mod_plr['id']);
            if (!$player) continue;

            $player->rating = $mod_plr['rating'];
            $player->rd = $mod_plr['rd'];
            $player->volatility = $mod_plr['volatility'];
            $player->save();
        }

        return $modified_players;
    }

    static function performRatingPeriodUpdate($player, $games) {
        $modified = [
            'id' => $player->id,
            'name' => $player->name,
            'rating' => $player->rating,
            'rd' => $player->rd,
            'volatility' => $player->volatility,
        ];

        $mu = getMu($player);
        $phi = getPhi($player);
        $sigma = $player->volatility;

        $v = 0;
        $rating_improvement = 0;
        foreach ($games as $game) {
            if ($player->id === $game->winner1_id || $player->id === $game->winner2_id) {
                $teammate = ($player->id === $game->winner1_id) ? $game->winner2 : $game->winner1;
                $opponents = [$game->loser1, $game->loser2];
                $score = normalizeScore($game->winner_score, $game->loser_score);
            } else {
                $teammate = ($player->id === $game->loser1_id) ? $game->loser2 : $game->loser1;
                $opponents = [$game->winner1, $game->winner2];
                $score = normalizeScore($game->loser_score, $game->winner_score);
            }


            $mu_j = getTeamMu($opponents[0], $opponents[1]);
            $phi_j = getTeamPhi($opponents[0], $opponents[1]);

            $tm_mu = getTeamMu($player, $teammate);

            $g = glickoG($phi_j);
            $e = glickoE($tm_mu, $mu_j, $phi_j);

            $e = min(max($e, 0.1), 0.9);

            $v += $g*$g * $e * (1-$e);
            $rating_improvement += $g * ($score - $e);
        }

        # Player did not compete
        if ($v == 0) {
            $phi_prime = sqrt($phi*$phi + $sigma*$sigma);
            $modified['rd'] = 173.7178 * $phi_prime;
            return $modified;
        }

        $v = 1/$v;
        $delta = $v * $rating_improvement;

        $sigma_prime = calculateSigma($sigma, $phi, $v, $delta);

        $phi_star = sqrt($phi*$phi + $sigma_prime * $sigma_prime);

        $phi_prime = 1/sqrt(1/($phi_star*$phi_star) + 1/$v);
        $mu_prime = $mu + $phi_prime * $phi_prime * $rating_improvement;

        $modified['rating'] = 173.7178 * $mu_prime + 1500;
        $modified['rd'] = 173.7178 * $phi_prime;
        $modified['volatility'] = $sigma_prime;

        return $modified;
    }
}
