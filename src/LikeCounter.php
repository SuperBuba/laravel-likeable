<?php

namespace Nano\Likeable;

use Illuminate\Database\Eloquent\Model as Eloquent;

class LikeCounter extends Eloquent
{
    protected $table = 'likeable_like_counters';
    public $timestamps = false;
    protected $fillable = ['likeable_id', 'likeable_type', 'count', 'type'];

    public function likeable($type = 'like')
    {
        return $this->morphTo()->where('type', $type);
    }

    /**
     * Delete all counts of the given model, and recount them and insert new counts
     *
     * @param $modelClass
     * @param string $type
     * @throws \Exception
     * @internal param string $model (should match Model::$morphClass)
     */
    public static function rebuild($modelClass, $type = 'like')
    {
        if (empty($modelClass)) {
            throw new \Exception('$modelClass cannot be empty/null. Maybe set the $morphClass variable on your model.');
        }

        $builder = Like::query()
            ->select(\DB::raw('count(*) as count, likeable_type, likeable_id, type'))
            ->where('likeable_type', $modelClass)
            ->where('type', $type)
            ->groupBy('likeable_id');

        $results = $builder->get();

        $inserts = $results->toArray();

        \DB::table((new static)->table)->insert($inserts);
    }

}
