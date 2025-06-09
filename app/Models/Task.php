<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task',
        'level',
        'priority',
        'start_date',
        'dateline',
        'notes',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'dateline' => 'date',
    ];
}
