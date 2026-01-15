<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectorAction extends Model
{
    protected $fillable = [
        'connector_id',
        'key',
        'label',
        'description',
        'category',
        'icon',
        'fields',
        'topic',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function connector()
    {
        return $this->belongsTo(Connector::class);
    }
}
