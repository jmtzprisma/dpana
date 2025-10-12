<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Controllers\Backend\Payments\PaymentsController;
use App\Http\Resources\Api\CartResource;
use App\Http\Resources\Api\OrderDetailsResource;
use App\Http\Resources\Api\OrderMiniResource;
use App\Http\Resources\Api\OrderPaymentMiniResource;
use App\Http\Resources\Api\TrackOrderResource;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Currency;
use App\Models\LogisticZone;
use App\Models\LogisticZoneCity;
use App\Models\MediaManager;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderPayments;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\RewardPoint;
use App\Models\ScheduledDeliveryTimeList;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Http\Request;
use Session;
use Str;
use Illuminate\Support\Facades\Crypt;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        if ($request->status != "all") {
            $orders = auth()->user()->orders()->where("delivery_status", $request->status)->latest()->paginate(25);
        } else {
            $orders = auth()->user()->orders()->latest()->paginate(25);
        }
        // return response()->json($orders);
        return OrderMiniResource::collection($orders);
        // return  OrderMiniResource::collection($orders);
    }

    public function getOrdersPayments(Request $request)
    {
        if ($request->status != "all") {
            $orders = auth()->user()->orders()->orderPayments()->where("status", 'pending')->latest()->paginate(25);
        } else {
            $orders = auth()->user()->orders()->latest()->paginate(25);
        }
        // return response()->json($orders);
        return OrderMiniResource::collection($orders);
        // return  OrderMiniResource::collection($orders);
    }

    #Order summery For checkout page
    public function summery(Request $request)
    {
        $shippingAmount = 0;
        if (isset($request->logistic_zone_id)) {
            $logisticZone       = LogisticZone::find((int)$request->logistic_zone_id);
            if ($logisticZone) {
                $shippingAmount     = $logisticZone->standard_delivery_charge;
            }
        }
        $carts = Cart::where('user_id', auth()->user()->id)->where('location_id', $request->header('Stock-Location-Id'))->get();
        $is_free_shipping = false;
        if (getCoupon() != '' && getCouponDiscount(getSubTotal($carts, false), getCoupon()) > 0) {
            $coupon = Coupon::where('code', getCoupon())->first();
            if (!is_null($coupon) && $coupon->is_free_shipping == 1) {
                $is_free_shipping = true;
            }
        }

        $shipping = 0;
        if (isset($shippingAmount) && $is_free_shipping == false) {
            $shipping = $shippingAmount;
        }
        if (count($carts) > 0) {
            checkCouponValidityForCheckout($carts);
        }

        $user = User::find(auth()->user()->id);

        return response()->json([
            'sub_total'     => formatPrice(getSubTotal($carts, false, '', false)),
            'tax'      => formatPrice(getTotalTax($carts)),
            'shipping_charge' => formatPrice($shippingAmount),
            'is_free_shipping' => $is_free_shipping,
            'limit_credit' => $user->limit_credit,
            'total_double' => formatPrice(getSubTotal($carts, false, '', false) + getTotalTax($carts) + $shipping - getCouponDiscount(getSubTotal($carts, false), getCoupon()), false, false, false),
            'coupon_discount' => formatPrice(getCouponDiscount(getSubTotal($carts, false), getCoupon())),
            'total' => formatPrice(getSubTotal($carts, false, '', false) + getTotalTax($carts) + $shipping - getCouponDiscount(getSubTotal($carts, false), getCoupon())),
        ]);
    }

    # Make an order
    public function store(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $carts  = Cart::where('user_id', $userId)->where('location_id', $request->header('Stock-Location-Id'))->get();

        if (count($carts) > 0) {

            # check if coupon applied -> validate coupon
            $couponResponse = checkCouponValidityForCheckout($carts);
            if ($couponResponse['status'] == false) {
                // flash($couponResponse['message'])->error();
                return $this->order_failed(localize($couponResponse['message']));
            }

            # check carts available stock -- todo::[update version] -> run this check while storing OrderItems
            foreach ($carts as $cart) {
                $productVariationStock = $cart->product_variation->product_variation_stock ? $cart->product_variation->product_variation_stock->stock_qty : 0;
                if ($cart->qty > $productVariationStock) {
                    $message = $cart->product_variation->product->collectLocalization('name') . ' ' . localize('is out of stock');

                    return $this->order_failed(localize($message));
                }
            }

            $comprobante = null;
            if ($request->hasFile('comprobante')) {
                $mediaFile = new MediaManager();
                $mediaFile->user_id = $user->id;
                $mediaFile->media_file = $request->file('comprobante')->store('uploads/media');
                $mediaFile->media_size = $request->file('comprobante')->getSize();
                $mediaFile->media_name = $request->file('comprobante')->getClientOriginalName();
                $mediaFile->media_extension = $request->file('comprobante')->getClientOriginalExtension();
                $mediaFile->kyc = true;

                if (getFileType(Str::lower($mediaFile->media_extension)) != null) {
                    $mediaFile->media_type = getFileType(Str::lower($mediaFile->media_extension));
                } else {
                    $mediaFile->media_type = "unknown";
                }
                $mediaFile->save();
                $comprobante = $mediaFile->id;
            }

            # create new order group
            $orderGroup                                     = new OrderGroup;
            $orderGroup->user_id                            = $userId;
            $orderGroup->shipping_address_id                = $request->shipping_address_id;
            $orderGroup->billing_address_id                 = $request->billing_address_id;
            $orderGroup->monto_plazo                        = $request->montoPlazo;
            $orderGroup->num_cuotas                         = $request->numCuotas;
            $orderGroup->method_payment                     = $request->methodId;
            $orderGroup->comprobante                       = $comprobante;
            $orderGroup->location_id                        = $request->header('Stock-Location-Id');
            $orderGroup->phone_no                           = $request->phone;
            $orderGroup->alternative_phone_no               = $request->alternative_phone;
            $orderGroup->sub_total_amount                   = getSubTotal($carts, false, '', false);
            $orderGroup->total_tax_amount                   = getTotalTax($carts);
            $orderGroup->total_coupon_discount_amount       = 0;
            if (getCoupon() != '') {
                # todo::[for eCommerce] handle coupon for multi vendor
                $orderGroup->total_coupon_discount_amount   = getCouponDiscount(getSubTotal($carts, false), getCoupon());
                # [done->codes below] increase coupon usage counter after successful order
            }

            $logisticZone = null;
            if($request->has('chosen_logistic_zone_id'))
            {
                $logisticZone = LogisticZone::where('id', $request->chosen_logistic_zone_id)->first();
                # todo::[for eCommerce] handle exceptions for standard & express
                $orderGroup->total_shipping_cost = $logisticZone->standard_delivery_charge;
            }else{
                $orderGroup->total_shipping_cost = 0;
            }

            // to convert input price to base price
            if ($request->hasHeader('Currency-Code')) {
                $currency_code = $request->header('Currency-Code');
            } else {
                $currency_code = env('DEFAULT_CURRENCY');
            }
            $currentCurrency = Currency::where('code', $currency_code)->first();
            

            $orderGroup->total_tips_amount                  = $request->tips / $currentCurrency->rate; // convert to base price;

            $orderGroup->grand_total_amount                 = $orderGroup->sub_total_amount + $orderGroup->total_tax_amount + $orderGroup->total_shipping_cost + $orderGroup->total_tips_amount - $orderGroup->total_coupon_discount_amount;


            if ($request->payment_method == "wallet") {
                $balance = (float) $user->user_balance;

                if ($balance < $orderGroup->grand_total_amount) {

                    return $this->failed(localize("Your wallet balance is low"));
                }
            }
            $orderGroup->save();

            # order -> todo::[update version] make array for each vendor, create order in loop
            $order = new Order;
            $order->order_group_id  = $orderGroup->id;
            $order->shop_id         = $carts[0]->product_variation->product->shop_id;
            $order->user_id         = $userId;
            $order->location_id     = $request->header('Stock-Location-Id');
            if ($request->hasHeader("Coupon-Code")) {
                $order->applied_coupon_code         = getCoupon();
                $order->coupon_discount_amount      = $orderGroup->total_coupon_discount_amount; // todo::[update version] calculate for each vendors
            }
            $order->total_admin_earnings            = $orderGroup->grand_total_amount;
            $order->logistic_id                     = is_null($logisticZone) ? null : $logisticZone->logistic_id;
            $order->logistic_name                   = is_null($logisticZone) ? null : optional($logisticZone->logistic)->name;
            $order->shipping_delivery_type          = $request->shipping_delivery_type;

            if ($request->shipping_delivery_type == getScheduledDeliveryType()) {
                $timeSlot = ScheduledDeliveryTimeList::where('id', $request->timeslot)->first(['id', 'timeline']);
                $timeSlot->scheduled_date = $request->scheduled_date;
                $order->scheduled_delivery_info = json_encode($timeSlot);
            }

            $order->shipping_cost                   = $orderGroup->total_shipping_cost; // todo::[update version] calculate for each vendors
            $order->tips_amount                     = $orderGroup->total_tips_amount; // todo::[update version] calculate for each vendors

            $order->save();


            $orderPayment                    = new OrderPayments;
            $orderPayment->ordergroup_id     = $orderGroup->id;
            $orderPayment->amount            = number_format($request->pagoInicial, 2);
            $orderPayment->status            = 'pending';
            $orderPayment->date_payment      = \Carbon\Carbon::now()->format('Y-m-d');
            
            $orderPayment->save();

            for($x = 1;$x <= $request->numCuotas; $x++)
            {
                $orderPayment                    = new OrderPayments;
                $orderPayment->ordergroup_id     = $orderGroup->id;
                $orderPayment->amount            = number_format($request->montoPlazo, 2);
                $orderPayment->status            = 'pending';
                $orderPayment->date_payment      = \Carbon\Carbon::now()->addDays((1*$x))->format('Y-m-d');
                
                $orderPayment->save();
            }

            # order items
            $total_points = 0;
            foreach ($carts as $cart) {
                $orderItem                       = new OrderItem;
                $orderItem->order_id             = $order->id;
                $orderItem->product_variation_id = $cart->product_variation_id;
                $orderItem->qty                  = $cart->qty;
                $orderItem->location_id          = $request->header('Stock-Location-Id');
                $orderItem->unit_price           = variationDiscountedPrice($cart->product_variation->product, $cart->product_variation);
                $orderItem->total_tax            = variationTaxAmount($cart->product_variation->product, $cart->product_variation);
                $orderItem->total_price          = $orderItem->unit_price * $orderItem->qty;
                $orderItem->save();

                $product = $cart->product_variation->product;
                $product->total_sale_count += $orderItem->qty;

                # reward points
                if (getSetting('enable_reward_points') == 1) {
                    $orderItem->reward_points = $product->reward_points * $orderItem->qty;
                    $total_points += $orderItem->reward_points;
                }

                // minus stock qty
                try {
                    $productVariationStock = $cart->product_variation->product_variation_stock;
                    $productVariationStock->stock_qty -= $orderItem->qty;
                    $productVariationStock->save();
                } catch (\Throwable $th) {
                    //throw $th;
                }
                $product->stock_qty -= $orderItem->qty;
                $product->save();

                # category sales count
                if ($product->categories()->count() > 0) {
                    foreach ($product->categories as $category) {
                        $category->total_sale_count += $orderItem->qty;
                        $category->save();
                    }
                }
                $cart->delete();
            }

            # reward points
            if (getSetting('enable_reward_points') == 1) {
                $reward = new RewardPoint;
                $reward->user_id = $userId;
                $reward->order_group_id = $orderGroup->id;
                $reward->total_points = $total_points;
                $reward->status = "pending";
                $reward->save();
            }

            $order->reward_points = $total_points;
            $order->save();

            # increase coupon usage
            if (getCoupon() != '' && $orderGroup->total_coupon_discount_amount > 0) {
                $coupon = Coupon::where('code', getCoupon())->first();
                $coupon->total_usage_count += 1;
                $coupon->save();

                # coupon usage by user
                $couponUsageByUser = CouponUsage::where('user_id', auth()->user()->id)->where('coupon_code', $coupon->code)->first();
                if (!is_null($couponUsageByUser)) {
                    $couponUsageByUser->usage_count += 1;
                } else {
                    $couponUsageByUser = new CouponUsage;
                    $couponUsageByUser->usage_count = 1;
                    $couponUsageByUser->coupon_code = getCoupon();
                    $couponUsageByUser->user_id = $userId;
                }
                $couponUsageByUser->save();
                // removeCoupon();
            }

            # payment gateway integration & redirection

            $orderGroup->payment_method = $request->payment_method;
            $orderGroup->save();
            return $this->order_complete($order->id);
        }

        return $this->order_failed(localize('Your cart is empty'));
    }

    # order successful
    public function order_success($code)
    {
        $orderGroup = OrderGroup::where('user_id', auth()->user()->id)->where('order_code', $code)->first();
        $user = auth()->user();

        // todo:: change this from here
        try {
            Notification::send($user, new OrderPlacedNotification($orderGroup->order));
        } catch (\Exception $e) {
        }
    }

    # order successful don't send notification
    public function order_complete($code)
    {
        //  $orderGroup = OrderGroup::where('user_id', auth()->user()->id)->where('order_code', $code)->first();

        return response()->json([
            "result" => true,
            "order_code" => $code,
            "message" => "Order success"
        ]);
    }

    #order failed
    public function order_failed($message)
    {
        return response()->json([
            "result" => true,
            "order_code" => 0,
            "message" => $message
        ]);
    }




    # order invoice
    public function invoice($code)
    {
        $order = Order::where('user_id', auth()->user()->id)->where('id', $code)->first();
        $orderGroup = OrderGroup::where('user_id', auth()->user()->id)->where('id', $order->order_group_id)->first();
        return new OrderDetailsResource($orderGroup);
        
    }

    # update payment status
    public function updatePayments($payment_details)
    {
        $orderGroup = OrderGroup::where('order_code', session('order_code'))->first();
        $payment_method = session('payment_method');

        $orderGroup->payment_status = paidPaymentStatus();
        $orderGroup->order->update(['payment_status' => paidPaymentStatus()]); # for multi-vendor loop through each orders & update

        $orderGroup->payment_method = $payment_method;
        $orderGroup->payment_details = $payment_details;
        $orderGroup->save();

        clearOrderSession();
        flash(localize('Your order has been placed successfully'))->success();
        return redirect()->route('checkout.success', $orderGroup->order_code);
    }

    public function orderByCOD(Request $request)
    {
        $request->payment_method = "cod";
        return $this->store($request);
    }

    public function orderByCuotas(Request $request)
    {
        $request->payment_method = "cuotas";
        return $this->store($request);
    }

    public function onlinePay(Request $request)
    {
        $orderGroup = OrderGroup::where('user_id', auth()->user()->id)->where('order_code', $request->code)->first();
            if ($request->payment_method != "cod" && $request->payment_method != "wallet") {
                Session::put('payment_type', 'order_payment');
                Session::put('order_code', $orderGroup->order_code);
                Session::put('payment_method', $request->payment_method);

                # init payment
                $payment = new PaymentsController;
                return $payment->initPayment();
            }
        

        return $this->order_failed(localize('Your cart is empty'));
    }


    public function orderByWallet(Request $request)
    {
        $request->payment_method = "wallet";
        return $this->store($request);
    }

    public function track(Request $request)
    {

        if ($request->code != null) {
            $searchCode = $request->code;
            $orderGroup = OrderGroup::where('order_code', $searchCode)->first();
            $order = null;

            if (!is_null($orderGroup)) {
                $order = Order::where('user_id', auth()->user()->id)->where('order_group_id', $orderGroup->id)->first();
            }

            if (is_null($order)) {
                return [
                    'data' => null
                ];
            }
            return new TrackOrderResource($order);
        }
    }
    
    public function inLocation($id)
    {
        $idD = Crypt::decryptString($id);

        $orders = Order::where('user_id', auth()->user()->id)->where('location_id', $idD)->get();
        foreach($orders as $itm)
        {
            $itm->user_in_location = true;
            $itm->save();
        }
        
        return response()->json([
            "result" => true,
            "message" => ''
        ]);
    }

    public function savePayment(Request $request){
        try{
        $user = auth()->user();
        $order = Order::find($request->orderId);

        if($request->has('paymentsToPay') && !is_null($request->paymentsToPay) && !empty($request->paymentsToPay)){
            foreach(explode(',', $request->paymentsToPay) as $itm){

                $order_payments = OrderPayments::where('ordergroup_id', $order->order_group_id)
                    ->where('id', $itm)
                    ->where('user_notify', false)->orderBy('id')->first();

                if($order_payments){
                    $comprobante = null;
                    if ($request->hasFile('comprobante')) {
                        $mediaFile = new MediaManager();
                        $mediaFile->user_id = $user->id;
                        $mediaFile->media_file = $request->file('comprobante')->store('uploads/media');
                        $mediaFile->media_size = $request->file('comprobante')->getSize();
                        $mediaFile->media_name = $request->file('comprobante')->getClientOriginalName();
                        $mediaFile->media_extension = $request->file('comprobante')->getClientOriginalExtension();
                        $mediaFile->kyc = true;

                        if (getFileType(Str::lower($mediaFile->media_extension)) != null) {
                            $mediaFile->media_type = getFileType(Str::lower($mediaFile->media_extension));
                        } else {
                            $mediaFile->media_type = "unknown";
                        }
                        $mediaFile->save();
                        $comprobante = $mediaFile->id;
                    }
                    
                    $amountBs = str_replace($request->amountBs,',', '.');
                    $order_payments->user_notify = true;
                    $order_payments->phone = $request->phone;
                    $order_payments->bank = $request->bank;
                    $order_payments->date = $request->date;
                    $order_payments->comprobante = $comprobante;
                    $order_payments->reference = $request->reference;
                    $order_payments->amountBs = is_numeric($amountBs) ? $amountBs : 0;
                    
                    $date = \Carbon\Carbon::parse($order_payments->date_payment);
                    if($date < \Carbon\Carbon::now()){
                        $order_payments->interest = '4.00';
                    }
                    $order_payments->save();
                }else{
                    return response()->json([
                        "result" => false,
                        "message" => ''
                    ]);
                }
            }
        }
        return response()->json([
            "result" => true,
            "message" => ''
        ]);
    }catch(\Exception $e){
        
        return response()->json([
            "result" => false,
            "message" => $e->getMessage()
        ]);
    }
    }
}
