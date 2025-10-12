<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\OrderItem;
use App\Models\ProductVariation;
use PayPalCheckoutSdk\Orders\OrdersValidateRequest;
use Carbon\Carbon;

class OrderPaymentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $month = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $date = Carbon::parse($this->date_payment);

        $vencimiento = ($this->status == 'paid') ? 'Aprobada' : ($date->format('Y-m-d') < Carbon::now()->format('Y-m-d') ? 'Vencida' : 'Próxima cuota');

        return [
            "id"=>$this->id,
            "status"=>$this->status == 'paid' ? 'Pagado' : 'Pendiente',
            "amount"=>formatPrice($this->amount),
            "user_notify"=>(boolean)$this->user_notify,
            "fecha_pago"=> $date->format('d') . ' ' . $month[intval($date->format('m'))],
            "vencimiento"=> $vencimiento
        ];
    }
}
