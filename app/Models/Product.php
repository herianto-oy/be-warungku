<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['user_id', 'code', 'name', 'img', 'price', 'stock'];

    public function getImgAttribute($value)
    {
        return  url('img/product', $value);
    }
}
