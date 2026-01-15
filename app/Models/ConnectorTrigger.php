<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectorTrigger extends Model
{
    protected $fillable = [
        'connector_id',
        'key',
        'label',
        'description',
        'type',
        'category',
        'icon',
        'topic',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function connector()
    {
        return $this->belongsTo(Connector::class);
    }
}
