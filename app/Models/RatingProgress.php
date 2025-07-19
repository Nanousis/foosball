<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingProgress extends Model
{
    protected $table = 'rating_progress';
    protected $fillable = ['current_rating_day'];
}
