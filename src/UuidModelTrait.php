<?php

namespace Alsofronie\Uuid;

use Webpatser\Uuid\Uuid;

/*
 * @package Alsofronie\Uuid
 * @author Alex Sofronie <alsofronie@gmail.com>
 * @license MIT
 */
trait UuidModelTrait
{
	/*
	 * This function is used internally by Eloquent models to test if the model has auto increment value
	 * @returns bool Always false
	 */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * This function overwrites the default boot static method of Eloquent models. It will hook
     * the creation event with a simple closure to insert the UUID
     */
    public static function boot() {

    	parent::boot();

        static::creating(function($model) {
            $model->attributes[$model->getKeyName()] = str_replace('-','', Uuid::generate(4));
        }, 0);
    }

}
