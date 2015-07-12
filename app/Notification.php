<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    protected $casts = [
        'payload' => 'array',
    ];
    public function chat(){
        return $this->belongsTo('\App\Chat', 'asso_id');
    }

}
