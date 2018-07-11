<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'blog';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [];

}