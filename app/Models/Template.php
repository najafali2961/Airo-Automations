<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'category', 'tags', 'connectors', 'workflow_data'];

    protected $casts = [
        'tags' => 'array',
        'connectors' => 'array',
        'workflow_data' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
