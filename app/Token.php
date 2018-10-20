<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id'
    ];

    /**
     * Get the user that owns the token.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

}