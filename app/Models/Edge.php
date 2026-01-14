<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edge extends Model
{
    protected $fillable = ['flow_id', 'source_node_id', 'target_node_id', 'label', 'source_handle'];
    
    public function sourceNode() {
        return $this->belongsTo(Node::class, 'source_node_id');
    }
    
    public function targetNode() {
        return $this->belongsTo(Node::class, 'target_node_id');
    }
}
