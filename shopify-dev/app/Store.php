<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'domain'
    ];    

    public function users()
    {
        return $this->belongsToMany(
            'App\User', 'store_users', 'store_id', 'user_id'
        );
    }
}
