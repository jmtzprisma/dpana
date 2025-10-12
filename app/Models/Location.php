<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    
    protected $casts = [
        'metodos_pago' => 'array',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'location_categories', 'location_id', 'category_id');
    }

}
