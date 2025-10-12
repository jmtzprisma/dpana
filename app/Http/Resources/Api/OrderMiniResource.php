<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\OrderItem;
use App\Models\ProductVariation;
use PayPalCheckoutSdk\Orders\OrdersValidateRequest;
use App\Http\Resources\Api\OrderPaymentsResource;
use App\Http\Resources\Api\LocationResource;

class OrderMiniResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            "id"=>$this->id,
            "location"=>new LocationResource($this->location),
            "items"=>new OrderMiniItemsResource($this->orderItems->first()),
            "payments" => OrderPaymentsResource::collection($this->orderPayments)??null,
            "status"=>$this->delivery_status,
            "payment_status"=>$this->payment_status,
            "order_code"=>$this->orderGroup->order_code,
            "num_cuotas"=>$this->orderGroup->num_cuotas,
            "monto_plazo"=>$this->orderGroup->monto_plazo,
            "grand_total_amount"=>$this->orderGroup->grand_total_amount,
            "date"=>$this->created_at,
        ];
    }
}
