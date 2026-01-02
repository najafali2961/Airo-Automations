<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExecutionLog extends Model
{
    protected $fillable = ['execution_id', 'node_id', 'level', 'message', 'data'];
    protected $casts = [
        'data' => 'array',
    ];

    public function execution()
    {
        return $this->belongsTo(Execution::class);
    }
}
