<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

     public function with($request)
     {
         return [
             'result' => true,
             'status' => 200
         ];
     }

    public function toArray($request)
    {
        $user = auth()->user();

        return [
            "id"=>$this->id ,
            "name"=>$this->name,
            "image"=> uploadedAsset($this->banner) ,
            "category_id"=>$this->category_id,
        ];
    }

}
