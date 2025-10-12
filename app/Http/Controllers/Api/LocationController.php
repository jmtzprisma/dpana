<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\LocationResource;
use App\Http\Resources\Api\CompanyResource;
use App\Models\Company;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariation;
use App\Models\ProductVariationStock;
use App\Models\WaitingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $company_id = $request->company_id;
        $city_id = $request->city_id;
        $locations = Location::where('is_published', 1)->where('city_id', $city_id);

        if($request->has('product_id') && !empty($request->product_id)){
            $product_variations_ids = ProductVariation::where('product_id', $request->product_id)->pluck('id');
            $variations_stk_ids = ProductVariationStock::whereIn('product_variation_id', $product_variations_ids)->where('stock_qty', '>', 0)->pluck('location_id');
            $locations = $locations->whereIn('id', $variations_stk_ids);
        }else{
            $locations = $locations->where('company_id', $company_id);
        }

        $locations = $locations->get();
        return LocationResource::collection($locations);
    }

    public function listCompanies(Request $request, $category_id, $subcategory_id)
    {
        $locations= Company::where('category_id', $category_id);
        
        if ($subcategory_id != 0) {
            $product_category_product_ids = ProductCategory::where('category_id', $subcategory_id)->pluck('product_id');
            $product_variation_id = ProductVariation::whereIn('product_id', $product_category_product_ids)->pluck('id');
            $product_variation_stock_locations = ProductVariationStock::whereIn('product_variation_id', $product_variation_id)->pluck('location_id');
            $companies = Location::whereIn('id', $product_variation_stock_locations)->pluck('company_id');
            $locations = $locations->whereIn('id', $companies);
        }
        $city_id = $request->city_id;
        $companies_active = Location::where('is_published', 1)->where('city_id', $city_id)->pluck('company_id');
        $locations = $locations->whereIn('id', $companies_active)->get();

        return CompanyResource::collection($locations);
    }

    public function getDetail($id)
    {
        $location= Location::find($id);

        return new LocationResource($location);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function saveWaitingList($loc_id){
        $location_id = Crypt::decryptString($loc_id);
        if(!WaitingList::where('location_id', $location_id)->where('user_id', auth()->user()->id)->exists()){
            $waiting_list = new WaitingList;
            $waiting_list->location_id = $location_id;
            $waiting_list->user_id = auth()->user()->id;
            $waiting_list->save();
            return response()->json([
                'result' => true,
                'message' => '']);
        }
        return response()->json([
            'result' => false,
            'message' => '']);
    }
}
