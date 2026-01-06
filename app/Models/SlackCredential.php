<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Added for the user relationship

class SlackCredential extends Model
{
    protected $fillable = [
        'user_id',
        'team_id',
        'team_name',
        'access_token',
        'refresh_token',
        'channel_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
