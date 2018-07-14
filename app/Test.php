<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Test extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [];

}