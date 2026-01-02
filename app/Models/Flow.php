<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flow extends Model
{
    protected $fillable = ['shop_id', 'name', 'description', 'active', 'execution_count', 'last_executed_at'];
    
    public function nodes() {
        return $this->hasMany(Node::class);
    }
    
    public function edges() {
        return $this->hasMany(Edge::class);
    }
    
    public function executions() {
        return $this->hasMany(Execution::class);
    }
    
    public function scopeActive($query) {
        return $query->where('active', true);
    }
}
