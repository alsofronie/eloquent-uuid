<?php

namespace Alsofronie\Uuid;

use Webpatser\Uuid\Uuid;

trait UuidModelTrait
{
    public function getIncrementing()
    {
        return false;
    }

    public static function boot() {

    	parent::boot();

        static::creating(function($model) {
            $model->attributes[$model->getKeyName()] = str_replace('-','', Uuid::generate(4));
        }, 0);
    }

}
