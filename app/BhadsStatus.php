<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class BhadsStatus extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'bh_ads_image_status';
    public $timestamps = false;

}
