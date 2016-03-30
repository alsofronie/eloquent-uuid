<?php

namespace Alsofronie\Uuid;

use Webpatser\Uuid\Uuid;

/*
 * This trait is to be used with $table->char('id',32) schema definition
 * It will simply strip the '-' from the uuid
 * @package Alsofronie\Uuid
 * @author Alex Sofronie <alsofronie@gmail.com>
 * @license MIT
 */
trait Uuid32ModelTrait
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
    public static function bootUuid32ModelTrait()
    {
        static::creating(function ($model) {

            // This is necessary because on \Illuminate\Database\Eloquent\Model::performInsert
            // will not check for $this->getIncrementing() but directly for $this->incrementing
            $model->incrementing = false;
            $uuidVersion = (!empty($model->uuidVersion) ? $model->uuidVersion : 4);   // defaults to 4
            $uuid = Uuid::generate($uuidVersion);
            $model->attributes[$model->getKeyName()] = str_replace('-', '', $uuid->string);
        }, 0);
    }
}
