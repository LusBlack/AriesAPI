<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'data', 'notifiable_id', 'notifiable_type', 'read_at', 'created_at', 'updated_at'
    ];
}
