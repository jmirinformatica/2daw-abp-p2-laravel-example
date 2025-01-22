<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'body'       => $this->body,
            'author'     => new UserResource($this->author),
            'status'     => new StatusResource($this->status),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Conditional data
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->whenCounted('comments'),
            'commented' => $this->commentedByAuthUser(),
            'likes_count'=> $this->whenCounted('likes'),
            'liked' => $this->likedByAuthUser()
        ];
    }
}
