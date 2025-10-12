<?php

namespace App\Http\Controllers\Api;

use \App\Models\Location;
use \App\Models\MethodsPayment;
use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;

class PaymentTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $payFor = "order";
        $paymentTypes= [];

        if ($request->has('payfor')) {
            $mode = $request->mode;
        }

        $location = Location::find(request()->header("Stock-Location-Id"));

        //if (getSetting('enable_cod') == 1 && $payFor=="order") {
            $tmp=[];
            $tmp['key']="cuotas";
            $tmp['num_cuotas']=(string)$location->cuotas;
            $tmp['name']=localize('Cuotas');
            $tmp['image']=staticAsset('frontend/pg/cod.svg');
            $paymentTypes[]=$tmp;
        //}

        if (getSetting('enable_cod') == 1 && $payFor=="order") {
            $tmp=[];
            $tmp['key']="cod";
            $tmp['num_cuotas']="0";
            $tmp['name']=localize('Pago de contado');
            $tmp['image']=staticAsset('frontend/pg/cod.svg');
            $paymentTypes[]=$tmp;

        }

        if (getSetting('enable_wallet_checkout') == 1 && $payFor=="order") {
            $tmp=[];
            $tmp['key']="wallet";
            $tmp['num_cuotas']="0";
            $tmp['name']=localize('Wallet Payment') ;
            $tmp['image']=staticAsset('frontend/pg/wallet.svg');
            $paymentTypes[]=$tmp;
        }


        // Paypal
        if (getSetting('enable_paypal') == 1) {
            $tmp=[];
            $tmp['key']="paypal";
            $tmp['num_cuotas']="0";
            $tmp['name']=localize('Pay with Paypal') ;
            $tmp['image']=staticAsset('frontend/pg/paypal.svg');
            $paymentTypes[]=$tmp;
        }

        // <!--Stripe-->
        if (getSetting('enable_stripe') == 1) {
            $tmp=[];
            $tmp['key']="stripe";
            $tmp['num_cuotas']="0";
            $tmp['name']=localize('Pay with Stripe') ;
            $tmp['image']=staticAsset('frontend/pg/stripe.svg');
            $paymentTypes[]=$tmp;
        }

        // <!--PayTm-->
        if (getSetting('enable_paytm') == 1) {
            $tmp=[];
            $tmp['key']="paytm";
            $tmp['num_cuotas']="0";
            $tmp['name']=localize('Pay with PayTm') ;
            $tmp['image']=staticAsset('frontend/pg/paytm.svg');
            $paymentTypes[]=$tmp;
        }

        // <!--Razorpay-->
        if (getSetting('enable_razorpay') == 1) {
            $tmp=[];
            $tmp['key']="razorpay";
            $tmp['num_cuotas']="0";
            $tmp['name']=localize('Pay with Razorpay') ;
            $tmp['image']=staticAsset('frontend/pg/razorpay.svg');
            $paymentTypes[]=$tmp;
        }

        // <!--iyzico-->
        if (getSetting('enable_iyzico') == 1) {
            $tmp=[];
            $tmp['key']="iyzico";
            $tmp['num_cuotas']="0";
            $tmp['name']=localize('Pay with IyZico') ;
            $tmp['image']=staticAsset('frontend/pg/iyzico.svg');
            $paymentTypes[]=$tmp;
        }


        return response()->json($paymentTypes);
    }

    public function getMethods(Request $request)
    {
        $location = Location::find(request()->header("Stock-Location-Id"));
        $paymentTypes= [];
        $methods = MethodsPayment::whereIn('id', $location->metodos_pago)->get();

        foreach($methods as $itm)
        {
            $tmp=[];
            $tmp['id']=$itm->id;
            $tmp['name']=$itm->name;
            $tmp['description']=$itm->description;
            $tmp['telefono']=$itm->telefono;
            $tmp['rif']=$itm->rif;
            $tmp['banco']=$itm->banco;
            $tmp['required_voucher']=$itm->required_voucher ? true : false;
            $tmp['image']= uploadedAsset($itm->image);
            $paymentTypes[]=$tmp;
        }

        return response()->json($paymentTypes);
    }

    /**
     * Show the form for creating a new resource.
     *
     * return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * param  \Illuminate\Http\Request  $request
     * return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * param  int  $id
     * return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * param  int  $id
     * return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * param  \Illuminate\Http\Request  $request
     * param  int  $id
     * return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * param  int  $id
     * return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
