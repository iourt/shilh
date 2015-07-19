<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    protected $casts = [
        'has_read' => 'boolean',
        'payload'  => 'array',
    ];
    protected $guarded = [];
    public function sender(){
        return $this->belongsTo('\App\User', 'sender_id');
    }

}
