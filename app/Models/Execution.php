<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Execution extends Model
{
    protected $fillable = [
        'flow_id', 'event', 'external_event_id', 'payload',
        'status', 'error_message', 'nodes_executed', 'actions_completed', 'duration_ms'
    ];
    protected $casts = ['payload' => 'json'];
    
    public function flow() {
        return $this->belongsTo(Flow::class);
    }
}
