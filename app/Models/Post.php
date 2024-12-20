<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Post extends Model {

	use HasFactory;

	protected $fillable = ['title', 'body', 'user_id', 'media_link', 'media_type'];

	public function user() {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

    public function comments() {
        return $this->hasMany(Comment::class, 'post_id');
    }


}
