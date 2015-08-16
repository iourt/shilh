<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserExpLog extends Model {

    protected $guarded = ['id'];
    protected $casts = [
        'data' => 'array',
    ];
}
