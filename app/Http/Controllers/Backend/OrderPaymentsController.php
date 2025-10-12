<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderPayments;
use App\Models\OrderGroup;
use App\Models\Location;
use App\Models\User;
use App\Models\Order;
use App\Models\Cart;
use Carbon\Carbon;


class OrderPaymentsController extends Controller
{
    public function index(Request $request)
    {
        $searchKey = null;
        $searchCode = null;
        $deliveryStatus = null;
        $paymentStatus = null;
        $locationId = null;
        $posOrder = 0;
        $userLocationId = auth()->user()->location_id;
        $isAdmin = auth()->user()->user_type == 'admin';

        // Get the orders, filtering by location if the user is not admin
        $orders = OrderPayments::latest()
            ->when(!$isAdmin, function ($query) use ($userLocationId) {
                return $query->whereHas('orderGroup', function ($q) use ($userLocationId) {
                    $q->whereNotNull('location_id') // Asegurar que existe location_id
                    ->where('location_id', $userLocationId);
                });
            });

        //Search by name or phone number
        if ($request->search != null) {
            $searchKey = $request->search;
            $customerIds = User::where('name', 'like', '%' . $searchKey . '%')
                ->orWhere('phone', 'like', '%' . $searchKey . '%')
                ->pluck('id')
                ->toArray();

            if (!empty($customerIds)) {
                $orders = $orders->whereIn('user_id', $customerIds);
            }
        }

        // Search by order code
        if ($request->code != null) {
            $searchCode = $request->code;
            $orderGroupIds = OrderGroup::where('order_code', $searchCode)
                ->pluck('id')
                ->toArray(); // Asegurar array válido

            if (!empty($orderGroupIds)) {
                $orders = $orders->whereIn('ordergroup_id', $orderGroupIds);
            }
        }

        // Filter by payment status
        if ($request->payment_status != null) {
            $paymentStatus = $request->payment_status;
            $orders = $orders->where('status', $paymentStatus);
        }

        // Filter by location if sent in the request
        if ($request->location_id != null) {
            $locationId = $request->location_id;
            $orders = $orders->whereHas('orderGroup', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        // Filter by POS orders
        $orderGroupIds = OrderGroup::where('is_pos_order', $posOrder)
            ->pluck('id')
            ->toArray();

        if (!empty($orderGroupIds)) {
            $orders = $orders->whereIn('ordergroup_id', $orderGroupIds);
        }

        // Paginate results
        $orderPayments = $orders->paginate(paginationNumber());

        // Get locations depending on the user type
        $locations = $isAdmin
            ? Location::latest()->where('is_published', 1)->get()
            : Location::where('id', $userLocationId)->where('is_published', 1)->get();

            $customers = User::where('user_type', 'customer')->where('is_banned', 0)->latest()->get();

        return view('backend.pages.order_payments.index', compact(
            'orderPayments', 'searchKey', 'locations', 'locationId',
            'searchCode', 'deliveryStatus', 'paymentStatus', 'posOrder', 'customers'
        ));
}


    public function pay($orderId)
    {
        $order = OrderPayments::findOrFail($orderId);
        $order->status = 'paid';
        $order->save();

        flash(localize('Order paid successfully'))->success();
        return back();
    }

    public function addOrder(Request $request)
    {
         # order group
         $orderGroup = new OrderGroup;
         $orderGroup->user_id = $request->order_customer_id;
         if ($request->order_customer_id != null) {
            $user = User::find((int)$request->order_customer_id);
            $orderGroup->phone_no     = $user->phone;
        }
        $orderGroup->location_id = auth()->user()->location_id;//$request->order_location_id;
        $orderGroup->sub_total_amount = $request->total_sale;
        $orderGroup->grand_total_amount = $request->total_sale;
        $orderGroup->payment_method = "cuotas";
        $orderGroup->payment_status = unpaidPaymentStatus();
        
        // if ($request->order_cuotas != null && $request->order_cuotas > 1) {
        //     $cuotas = $request->order_cuotas - 1;
        //     $saldo_faltante = $request->total_sale * 0.40;
        //     $amount_cuotas = $saldo_faltante / $cuotas;
        // } else {
            $amount_cuotas = $request->total_sale * 0.40; 
        //}
        
        $orderGroup->monto_plazo = $amount_cuotas;
        $orderGroup->num_cuotas = 1; //$request->order_cuotas;
        $orderGroup->method_payment = 1;
        $orderGroup->total_shipping_cost  = 0;
        $orderGroup->save();

         # order
         $order = new Order;
         $order->order_group_id  = $orderGroup->id;
         $order->shop_id         = 1;
         $order->user_id         = $orderGroup->user_id;
         $order->location_id     = $orderGroup->location_id;
         $order->total_admin_earnings            = $orderGroup->grand_total_amount;
         $order->shipping_cost                   = $orderGroup->total_shipping_cost;
         $order->delivery_status                   = 'order_placed';
         $order->payment_status                   = unpaidPaymentStatus();
         $order->save();

        $orderGroup->order_code = $order->id;
        $orderGroup->save();
        
        $orderPayment                    = new OrderPayments;
        $orderPayment->ordergroup_id     = $orderGroup->id;
        $orderPayment->amount            = $request->total_sale * 0.60;
        $orderPayment->status            = 'paid';
        $orderPayment->date_payment      = \Carbon\Carbon::now()->format('Y-m-d');
        $orderPayment->save();

        $x = 1;
        // for($x = 1;$x < $request->order_cuotas; $x++)
        // {
            $orderPayment                    = new OrderPayments;
            $orderPayment->ordergroup_id     = $orderGroup->id;
            $orderPayment->amount            = number_format($orderGroup->monto_plazo, 2);
            $orderPayment->status            = 'pending';
            $orderPayment->date_payment      = \Carbon\Carbon::now()->addDays((1*$x))->format('Y-m-d');
            $orderPayment->save();
        // }
        flash(localize('Your order has been placed successfully'))->success();
        return redirect()->route('admin.orderspayments.index');
    }

    public function getUser(Request $request){
        $user = User::where('user_type', 'customer')->where('is_banned', 0)->where('email', $request->email)->first();
        $dt_now = Carbon::now();
        if($user){
            $orders_pending = OrderPayments:://where('user_id', $user->id)
                            when(true, function ($query) use ($user) {
                                return $query->whereHas('orderGroup', function ($q) use ($user) {
                                    $q->where('user_id', $user->id);
                                });
                            })
                            ->where('status', 'pending')
                            ->where('date_payment', '<', $dt_now->format('Y-m-d'))
                            ->first();

            $carts          = Cart::where('user_id', $user->id)->get();
            $amount_used = OrderPayments::whereIn('ordergroup_id', $user->orderGroups()->pluck('id'))
                                ->where('status', 'pending')->sum('amount') + (getSubTotal($carts, false, '') * .4);

            return response()->json([
                "result"        => true,
                "amount_used"   => $amount_used,
                "orders_pending" => $orders_pending ? true : false,
                "user"          => json_encode($user)
            ]);
        }else
            return response()->json([
                "result" => false,
                "message" => 'Usuario no encontrado'
            ], 400);
    }

}
