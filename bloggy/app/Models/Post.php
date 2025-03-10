<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'author_id',
        'status_id'
    ];
    
    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function commented()
    {
        return $this->belongsToMany(User::class, 'comments');
    }
    
    public function commentedByUser(User $user)
    {
        $count = Comment::where([
            ['author_id',  '=', $user->id],
            ['post_id', '=', $this->id],
        ])->count();

        return $count > 0;
    }

    public function commentedByAuthUser()
    {
        $user = auth()->user() ?: auth('sanctum')->user();
        return $user && $this->commentedByUser($user);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    
    public function isPublished()
    {
        return $this->status_id === Status::PUBLISHED;
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function liked()
    {
        return $this->belongsToMany(User::class, 'likes');
    }
    
    public function likedByUser(User $user)
    {
        $count = Like::where([
            ['user_id',  '=', $user->id],
            ['post_id', '=', $this->id],
        ])->count();
        
        return $count > 0;
    }

    public function likedByAuthUser()
    {
        $user = auth()->user() ?: auth('sanctum')->user();
        return $user && $this->likedByUser($user);
    }
}
