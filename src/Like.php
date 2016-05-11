<?php

namespace Nano\Likeable;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Like extends Eloquent
{
    protected $table = 'likeable_likes';
    public $timestamps = true;
    protected $fillable = ['likeable_id', 'likeable_type', 'user_id', 'type'];

    public function likeable($type = 'like')
    {
        return $this->morphTo()->where('type', $type);
    }
}