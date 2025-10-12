<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\CartResource;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Location;
use App\Models\ProductVariation;
use App\Models\User;
use App\Models\OrderPayments;
use Illuminate\Http\Request;

class CartController extends Controller
{
    # all cart items
    public function index(Request $request)
    {
        return $this->getCartsInfo();
    }

    # add to cart
    public function store(Request $request)
    {
        $productVariation = ProductVariation::where('id', $request->product_variation_id)->first();

        $user = User::find(auth()->user()->id);
        $carts          = Cart::where('user_id', auth()->user()->id)->where('location_id', request()->header("Stock-Location-Id"))->get();
        if(number_format(OrderPayments::whereIn('ordergroup_id', $user->orderGroups()->pluck('id'))->where('status', 'pending')->sum('amount') + getSubTotal($carts, false, '') + $productVariation->price, 2, '.','') > number_format($user->limit_credit, 2, '.','')){
            $message =  localize('Limite de crédito insuficiente');
            return $this->getCartsInfo($message, false, '', false);
        }

        if (!is_null($productVariation)) {

            $cart = null;
            $message = '';
            $cart          = Cart::where('user_id', auth()->user()->id)->where('location_id', $request->header("Stock-Location-Id"))->where('product_variation_id', $productVariation->id)->first();


            if (is_null($cart)) {
                $cart = new Cart;
                $cart->product_variation_id = $productVariation->id;
                $cart->qty                  = (int) $request->quantity;
                $cart->location_id          = $request->header("Stock-Location-Id");

                if (auth()->check()) {
                    $cart->user_id          = auth()->user()->id;
                } else {
                    $cart->guest_user_id    = (int) $_COOKIE['guest_user_id'];
                }
                $message =  localize('Producto agregado a tu carrito');
            } else {
                $cart->qty                  += (int) $request->quantity;
                $message =  localize('La cantidad fue incrementada');
            }

            $cart->save();
            return $this->getCartsInfo($message, false);
        }
    }

    # update cart
    public function update(Request $request)
    {
        //try {
            
            $user = User::find(auth()->user()->id);
            $cart = Cart::where('id', $request->id)->first();
            $carts          = Cart::where('user_id', auth()->user()->id)->where('location_id', request()->header("Stock-Location-Id"))->get();
            if($request->action == "increase" && number_format(OrderPayments::whereIn('ordergroup_id', $user->orderGroups()->pluck('id'))->where('status', 'pending')->sum('amount') + getSubTotal($carts, false, '') + $cart->product_variation->price, 2, '.', '') > number_format($user->limit_credit, 2, '.', '')){
                $message =  localize('Limite de crédito insuficiente');
                return $this->getCartsInfo($message, false, '', false);
            }


            if ($request->action == "increase") {
                $productVariationStock = $cart->product_variation->product_variation_stock;
                if ($productVariationStock->stock_qty > $cart->qty) {
                    $cart->qty += 1;
                    $cart->save();
                }
            } elseif ($request->action == "decrease") {
                if ($cart->qty > 1) {
                    $cart->qty -= 1;
                    $cart->save();
                }else{
                    $cart->delete();
                }
            } elseif ($request->action == "delete") {
                $cart->delete();
            }
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }

        //removeCoupon();
        // return $this->success('Updated');
        return $this->getCartsInfo('Updated', false);
    }

    public function clearCart(Request $request)
    {
        try {
            $carts = Cart::where('user_id', auth()->user()->id)->get();
            foreach($carts as $cart)
            {
                $cart->delete();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        //removeCoupon();
        // return $this->success('Updated');
        return $this->getCartsInfo('Updated', false);
    }

    # apply coupon
    public function applyCoupon(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();
        if ($coupon) {
            $date = strtotime(date('d-m-Y H:i:s'));

            # check if coupon is not expired
            if ($coupon->start_date <= $date && $coupon->end_date >= $date) {

                $carts  = Cart::where('user_id', auth()->user()->id)->where('location_id', $request->header("Stock-Location-Id"))->get();


                # check min spend
                $subTotal = (float) getSubTotal($carts, false);
                if ($subTotal >= (float) $coupon->min_spend) {

                    # check if coupon is for categories or products
                    if ($coupon->product_ids || $coupon->category_ids) {
                        if ($carts && validateCouponForProductsAndCategories($carts, $coupon)) {
                            # SUCCESS:: can apply coupon
                            // setCoupon($coupon);
                            // return $this->success(localize('Coupon applied successfully'));
                            return $this->getCartsInfo(localize('Coupon applied successfully'), true, $coupon->code);
                        }

                        # coupon not valid for your cart items
                        // removeCoupon();
                        // return $this->couponApplyFailed(localize('Coupon is only applicable for selected products or categories'));
                        return $this->couponApplyFailed(localize('Coupon is only applicable for selected products or categories'));
                    }

                    # SUCCESS::can apply coupon - not product or category based
                    // setCoupon($coupon);
                    return $this->getCartsInfo(localize('Coupon applied successfully'), true, $coupon->code);
                }

                # min spend
                //    removeCoupon();
                return $this->couponApplyFailed('Please shop for atleast ' . formatPrice($coupon->min_spend));
            }

            # expired
            //    removeCoupon();
            return $this->couponApplyFailed(localize('Coupon is expired'));
        }

        // coupon not found
        //    removeCoupon();
        return $this->couponApplyFailed(localize('Coupon is not valid'));
    }

    # coupon apply failed
    private function couponApplyFailed($message = '', $success = false)
    {
        $response = $this->getCartsInfo($message, false);
        $response['success'] = $success;
        return $response;
    }

    # clear coupon
    public function clearCoupon()
    {
        return $this->couponApplyFailed(localize('Coupon has been removed'), true);
    }

    # get cart information
    private function getCartsInfo($message = '', $couponDiscount = true, $couponCode = '', $result = true)
    {
        $carts          = Cart::where('user_id', auth()->user()->id)->where('location_id', request()->header("Stock-Location-Id"))->get();
        $user = User::find(auth()->user()->id);

        $amount_used = OrderPayments::whereIn('ordergroup_id', $user->orderGroups()->pluck('id'))
                            ->where('status', 'pending')->sum('amount') + (getSubTotal($carts, $couponDiscount, $couponCode) * .4);

        $location = Location::find(request()->header("Stock-Location-Id"));
        return [
            'result'            => $result,
            'name_location'     => $location->name,
            'message'           => $message,
            'carts'             => CartResource::collection($carts),
            'cartCount'         => count($carts),
            'total'             => formatPrice(getSubTotal($carts, $couponDiscount, $couponCode)),
            'subTotal'          => formatPrice(getSubTotal($carts, false, "")),
            'couponDiscount'    => formatPrice(getCouponDiscount(getSubTotal($carts, false), $couponCode)),
            'limit'             => number_format($user->limit_credit, 2,'.',''),
            'used'              => number_format($amount_used, 2,'.','')
        ];
    }
}
