<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    //
    protected $fillable = [
        'id', 'name', 'description', 'category'
    ];

    protected $hidden = [];
}
