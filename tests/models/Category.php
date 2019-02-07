<?php

namespace Fureev\Trees\Tests\models;

use Fureev\Trees\NestedSetTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 *
 * @package Fureev\Trees\Tests\models
 * @property int $id
 * @property string $name
 * @property int $lvl
 * @mixin \Fureev\Trees\QueryBuilder
 */
class Category extends Model
{
    use NestedSetTrait;

    protected $fillable = ['name', '_setRoot'];

    public $timestamps = false;

    protected $table = 'categories';

    /* public static function resetActionsPerformed()
     {
         static::$actionsPerformed = 0;
     }*/
}