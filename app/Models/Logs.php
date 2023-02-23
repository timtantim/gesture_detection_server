<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory;

    
    protected $table = 'logs';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'user_account',
        'file',
        'line',
        'payload',
        'status',
        'response',
        'note',
        'created_at',
        'updateed_at',
    ];
}
