<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;

class LocationResource extends JsonResource
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

        $tiempo_transcurrido_en_m = "0";

        if($user->lat && $user->lng && $this->lat && $this->lng)
        {
            $distance = DB::select("SELECT (acos(sin(radians(".$this->lat.")) * sin(radians(".$user->lat .")) + 
                                            cos(radians(".$this->lat.")) * cos(radians(".$user->lat .")) * 
                                            cos(radians(".$this->lng.") - radians(".$user->lng ."))) * 6378) as d")[0];

            $distancia_total_en_km = floatval($distance->d);
            $velocidad_promedio_en_kh = floatval(70);
            $tiempo_transcurrido_en_m=60.0*$distancia_total_en_km/$velocidad_promedio_en_kh;
        }

        return [
            "id"=>$this->id ,
            "name"=>$this->name,
            "image"=> uploadedAsset($this->banner) ,
            "address"=>$this->address,
            "phone"=>$this->phone ?? '',
            "lat"=>$this->lat,
            "lng"=>$this->lng,
            "horario"=>(string)$this->horario,
            "tiempo_distancia"=>(string)$tiempo_transcurrido_en_m,
            "compra_online" => $this->compra_online,
            "compra_qr" => $this->compra_qr,
            "cuotas" => $this->cuotas,
            "is_default"=>$this->is_default==1,
        ];
    }

}
