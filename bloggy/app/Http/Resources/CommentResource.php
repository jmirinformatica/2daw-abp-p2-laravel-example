<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'comment'    => $this->comment,
            'user'       => new UserResource($this->user),
            'post'       => new PostResource($this->post),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
