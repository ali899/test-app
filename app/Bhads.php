<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Bhads extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'bh_ads';
    public $timestamps = false;
//    public function images()
//    {
//        return $this->hasMany(BhadsStatus::class,'adCode','adCode');
//    }
}
