<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'firstName',
        'LastName',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // public function courses() {
    //  return $this->hasMany(Courses::class, 'user_id');
    // }

    public function getRouteKeyName() {
        return 'username';
    }

    public function posts() {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function profile() {
        return $this->belongsTo(Profile::class, 'user_id');
    }

    public function likes() {
        return $this->belongsTo(Like::class, 'user_id');
    }



    // Remove the tokens method
}
