<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConnector extends Model
{
    protected $fillable = [
        'user_id',
        'connector_slug',
        'is_active',
        'credentials',
        'expires_at',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'credentials' => 'encrypted:array', // Auto-encrypt on save, decrypt on access
        'meta' => 'array',
    ];

    /**
     * Get the user who owns the connection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
