<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    protected $fillable = ['flow_id', 'type', 'settings', 'label', 'position_x', 'position_y'];
    protected $casts = ['settings' => 'json'];
    
    public function flow() {
        return $this->belongsTo(Flow::class);
    }
    
    // Helper to find outgoing edges
    public function edges() {
        return Edge::where('source_node_id', $this->id);
    }
    
    // Helper to find connected next nodes via edges
    public function nextNodes($label = null) {
        $query = $this->edges();
        if ($label) {
            $query->where('label', $label);
        }
        return $query->with('targetNode')->get()->pluck('targetNode');
    }
}
