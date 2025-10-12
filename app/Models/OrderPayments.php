<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayments extends Model
{
    use HasFactory;
    
    public function orderGroup()
    {
        return $this->belongsTo(OrderGroup::class, 'ordergroup_id', 'id');
    }
}
