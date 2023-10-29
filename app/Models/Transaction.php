<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $fillable = ['user_id', 'name', 'timestamp', 'total', 'status'];


    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }
}
