<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProducts implements ToModel
{
   
    public function model(array $row)
    {
        return new Product([
            'shop_id' => $row[0],
            'added_by' => $row[1],
            'name' => $row[2],
            'slug' => $row[3],
            'min_price' => $row[4],
            'max_price' => $row[5],
            'discount_value' => $row[6],
            'stock_qty' =>$row[7]
        ]);
    }
}
