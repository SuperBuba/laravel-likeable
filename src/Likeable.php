<?php

namespace Nano\Likeable;

/**
 * Copyright (C) 2014 Robert Nano
 */
trait Likeable
{
    /**
     * Boot the soft taggable trait for a model.
     *
     * @return void
     */
    public static function bootLikeable()
    {
        if (static::removeLikesOnDelete()) {
            static::deleting(function ($model) {
                $model->removeLikes();
            });
        }
    }

    /**
     * Fetch records that are liked by a given user.
     * Ex: Book::whereLikedBy(123)->get();
     */
    public function scopeWhereLikedBy($query, $userId = null, $type = 'like')
    {
        if (is_null($userId)) {
            $userId = $this->loggedInUserId();
        }

        return $query->whereHas('likes', function ($q) use ($userId, $type) {
            $q->where('user_id', '=', $userId)
                ->where('type', $type);
        });
    }


    /**
     * Populate the $model->likes attribute
     * @param string $type
     * @return int
     */
    public function getLikeCountAttribute()
    {
        return $this->likeCounter ? $this->likeCounter->count : 0;
    }

    /**
     * Populate the $model->likes attribute
     * @param string $type
     * @return int
     */
    public function getDislikeCountAttribute()
    {
        return $this->dislikeCounter ? $this->dislikeCounter->count : 0;
    }

    /**
     * Collection of the likes on this record
     * @param string $type
     * @return
     */
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable')->where('type', 'like');
    }

    /**
     * Collection of the likes on this record
     * @param string $type
     * @return
     */
    public function dislikes()
    {
        return $this->morphMany(Like::class, 'likeable')->where('type', 'dislike');
    }

    /**
     * Counter is a record that stores the total likes for the
     * morphed record
     * @param string $type
     * @return
     */
    public function likeCounter()
    {
        return $this->morphOne(LikeCounter::class, 'likeable')->where('type', 'like');
    }

    /**
     * Counter is a record that stores the total likes for the
     * morphed record
     * @param string $type
     * @return
     */
    public function dislikeCounter()
    {
        return $this->morphOne(LikeCounter::class, 'likeable')->where('type', 'dislike');
    }

    /**
     * Add a like for this record by the given user.
     * @param string $type
     * @param $userId mixed - If null will use currently logged in user.
     */
    public function like($type = 'like', $userId = null)
    {
        if (is_null($userId)) {
            $userId = $this->loggedInUserId();
        }

        if ($userId) {
            $like = $this->likes($type)
                ->where('user_id', '=', $userId)
                ->where('type', $type)
                ->first();

            if ($like) {
                return;
            }

            $like = new Like();
            $like->user_id = $userId;
            $like->type = $type;
            $this->likes($type)->save($like);
        }

        $this->incrementLikeCount($type);
    }

    public function dislike($userId = null)
    {

        $this->like('dislike', $userId);

    }

    /**
     * Remove a like from this record for the given user.
     * @param string $type
     * @param $userId mixed - If null will use currently logged in user.
     */
    public function unlike($type = 'like', $userId = null)
    {
        if (is_null($userId)) {
            $userId = $this->loggedInUserId();
        }

        if ($userId) {
            $like = $this->likes($type)
                ->where('user_id', '=', $userId)
                ->where('type', $type)
                ->first();

            if (!$like) {
                return;
            }

            $like->delete();
        }

        $this->decrementLikeCount($type);
    }

    public function undislike($userId = null)
    {

        $this->unlike('dislike', $userId);

    }


    /**
     * Has the currently logged in user already "liked" the current object
     *
     * @param string $userId
     * @param string $type
     * @return bool
     */
    public function liked($type = 'like', $userId = null)
    {
        if (is_null($userId)) {
            $userId = $this->loggedInUserId();
        }

        return (bool)$this->likes()
            ->where('user_id', '=', $userId)
            ->where('type', $type)
            ->count();
    }

    /**
     * Private. Increment the total like count stored in the counter
     * @param string $type
     */
    private function incrementLikeCount($type = 'like')
    {
        $counter = $this->likeCounter($type)->first();

        if ($counter) {
            $counter->count++;
            $counter->save();
        } else {
            $counter = new LikeCounter;
            $counter->type = $type;
            $counter->count = 1;
            $this->likeCounter($type)->save($counter);
        }
    }

    /**
     * Private. Decrement the total like count stored in the counter
     * @param $type
     */
    private function decrementLikeCount($type)
    {
        $counter = $this->likeCounter($type)->first();

        if ($counter) {
            $counter->count--;
            if ($counter->count) {
                $counter->save();
            } else {
                $counter->delete();
            }
        }
    }

    /**
     * Fetch the primary ID of the currently logged in user
     * @return number
     */
    public function loggedInUserId()
    {
        return auth()->id();
    }

    /**
     * Did the currently logged in user like this model
     * Example : if($book->liked) { }
     * @param string $type
     * @return bool
     */
    public function getLikedAttribute($type = 'like')
    {
        return $this->liked($type);
    }

    /**
     * Should remove likes on model row delete (defaults to true)
     * public static removeLikesOnDelete = false;
     */
    public static function removeLikesOnDelete()
    {
        return isset(static::$removeLikesOnDelete)
            ? static::$removeLikesOnDelete
            : true;
    }

    /**
     * Delete likes related to the current record
     * @param string $type
     */
    public function removeLikes($type = 'like')
    {
        Like::where('likeable_type', $this->morphClass)
            ->where('likeable_id', $this->id)
            ->where('type', $type)
            ->delete();

        LikeCounter::where('likeable_type', $this->morphClass)
            ->where('likeable_id', $this->id)
            ->where('type', $type)
            ->delete();
    }
}
