<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function triggers()
    {
        return $this->hasMany(ConnectorTrigger::class);
    }

    public function actions()
    {
        return $this->hasMany(ConnectorAction::class);
    }
}
