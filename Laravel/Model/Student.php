<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'user_id',
        'program_version',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo('App\ModelAdapters\UserAdapter', 'user_id');
    }
}
