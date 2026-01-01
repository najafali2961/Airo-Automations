<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'n8n_id',
        'name',
        'ui_data',
        'status',
    ];

    protected $casts = [
        'ui_data' => 'array',
        'status' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }
}
