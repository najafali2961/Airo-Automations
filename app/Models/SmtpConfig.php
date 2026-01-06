<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SmtpConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
