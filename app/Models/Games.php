<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Games extends Model
{
    protected $fillable = [
        'winner1_id',
        'winner2_id',
        'loser1_id',
        'loser2_id',
        'winner_score',
        'loser_score',
        'winner1_elo_change',
        'winner2_elo_change',
        'loser1_elo_change',
        'loser2_elo_change',
    ];

    // Define relationships
    public function winner1()
    {
        return $this->belongsTo(Players::class, 'winner1_id');
    }

    public function winner2()
    {
        return $this->belongsTo(Players::class, 'winner2_id');
    }

    public function loser1()
    {
        return $this->belongsTo(Players::class, 'loser1_id');
    }

    public function loser2()
    {
        return $this->belongsTo(Players::class, 'loser2_id');
    }
}
