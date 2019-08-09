<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }


    protected $fillable = [
        'content','image','expireDate'
    ];
}
