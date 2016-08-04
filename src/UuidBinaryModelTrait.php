<?php

namespace Alsofronie\Uuid;

use Webpatser\Uuid\Uuid;

/*
 * This trait is to be used with the DB::statement('ALTER TABLE table_name ADD COLUMN id BINARY(16) PRIMARY KEY')
 * @package Alsofronie\Uuid
 * @author Alex Sofronie <alsofronie@gmail.com>
 * @license MIT
 */
trait UuidBinaryModelTrait
{
    protected static $primaryColumn = 'id';

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
    public static function bootUuidBinaryModelTrait()
    {
        static::creating(function ($model) {
            // This is necessary because on \Illuminate\Database\Eloquent\Model::performInsert
            // will not check for $this->getIncrementing() but directly for $this->incrementing
            $model->incrementing = false;
            $uuidVersion = (!empty($model->uuidVersion) ? $model->uuidVersion : 4);   // defaults to 4
            $uuid = Uuid::generate($uuidVersion);
            $model->attributes[$model->getKeyName()] = (property_exists($model, 'uuidOptimization') && $model::$uuidOptimization ? $model::toOptimized($uuid->string) : $uuid->bytes);
        }, 0);
    }

    /**
     * Gets the binary field as hex string ($model->id_string)
     * @return string The string representation of the binary field.
     */
    public function getIdStringAttribute()
    {
        return (property_exists($this, 'uuidOptimization') && $this::$uuidOptimization)
            ? self::toNormal($this->attributes[self::$primaryColumn]) : bin2hex($this->attributes[self::$primaryColumn]);
    }

    /**
     * Modified find static function to accept both string and binary versions of uuid
     * @param  mixed $id       The id (binary or hex string)
     * @param  array $columns  The columns to be returned (defaults to *)
     * @return mixed           The model or null
     */
    public static function find($id, $columns = array('*'))
    {
        if (ctype_print($id)) {
            $idFinal = (property_exists(static::class, 'uuidOptimization') && static::$uuidOptimization)
            ? self::toOptimized($id) : hex2bin($id);

            return static::where(self::$primaryColumn, '=', $idFinal)->first($columns);
        } else {
            return parent::where(self::$primaryColumn, '=', $id)->first($columns);
        }
    }

    /**
     * Convert uuid string (with or without dashes) to binary
     * @param  string $uuid
     * @return binary
     */
    public static function toOptimized($uuid)
    {
        $uuid = preg_replace('/\-/', null, $uuid);
        return hex2bin(substr($uuid, 12, 4)) .
            hex2bin(substr($uuid, 8, 4)) .
            hex2bin(substr($uuid, 0, 8)) .
            hex2bin(substr($uuid, 16, 4)) .
            hex2bin(substr($uuid, 20));
    }

    /**
     * Convert uuid binary to string (without dashes)
     * @param  binary $uuid
     * @return string
     */
    public static function toNormal($uuid)
    {
        return bin2hex(substr($uuid, 4, 4)) .
            bin2hex(substr($uuid, 2, 2)) .
            bin2hex(substr($uuid, 0, 2)) .
            bin2hex(substr($uuid, 8, 2)) .
            bin2hex(substr($uuid, 10));
    }
}

