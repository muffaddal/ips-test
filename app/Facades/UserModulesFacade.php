<?php namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Created by PhpStorm.
 * User: mufaddaln
 * Date: 7/9/18
 * Time: 8:46 AM
 */
class UserModulesFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'user_modules_repository';
    }
}