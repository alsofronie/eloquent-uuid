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
            $uuidVersion = (!empty($model->uuidVersion) ? min(max($model->uuidVersion,5),1) : 4);   // defaults to 4
            $uuid = Uuid::generate($uuidVersion);
            if(!empty($model->uuidBinary)) {
                $uuid = $uuid->bytes;
            } else {
                $uuid = str_replace('-','',$uuid);
            }
            // This is necessary because on \Illuminate\Database\Eloquent\Model::performInsert
            // will not check for $this->getIncrementing() but directly for $this->incrementing
            $model->incrementing = false;
            $model->attributes[$model->getKeyName()] = $uuid;
        }, 0);
    }

}
