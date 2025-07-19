<?php

namespace App\Cli;
use App\Models\Players;

use App\Utils\Glicko2;

const TAU = 0.2;
const EPSILON = 0.000001;

const STARTING_RATING = 1500;
const STARTING_RD = 350;
const STARTING_VOLATILITY = 0.08;

class GlickoDemo {


    public static function runDemo() {
        $players = Players::orderBy('elo', 'desc')->get();

        $plr = Players::where('name', 'Pietro')->get()[0];

        Glicko2::updateElo($plr, 0, 0, 0, 0);

        // foreach ($players as $player) {
        //     echo "$player->name\n";
        //     $player->save();
        // }
    }
}
