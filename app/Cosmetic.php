<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cosmetic extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function packages(){
        return $this->belongsToMany(Package::class);
    }

    public function Order(){
        return $this->belongsToMany(Order::class);
    }

    protected $fillable =[
        'name','price','image','description'
    ];
}
