<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\ProductDetailsResource;
use App\Http\Resources\Api\ProductMiniResource;
use App\Models\LocationCategories;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariationStock;
use App\Models\ProductVariation;
use App\Models\ProductTag;
use App\Models\Tag;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchKey = null;
        $sort_by = $request->sort_by ? $request->sort_by : "new";
        $maxRange = Product::max('max_price');
        $min_value = 0;
        $per_page = 15;
        $max_value = $maxRange;

        $products = Product::isPublished();

        # conditional - search by
        if ($request->search != null) {
            //$products = $products->where('name', 'like', '%' . $request->search . '%');
            $products = $products->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('slug', '%' . $request->search . '%')
                            ->orWhere('product_tags', '%' . $request->search . '%')
                // ->where(function ($qry) {
                //     $qry->orWhere('slug', 'pending')
                //         ->orWhere('product_tags', 'prepair')
                //         ->orWhere('delivery_status', 'on_the_way');
                    
                // })
                //->get()
                ;
        }

        # pagination
        if ($request->per_page != null) {
            $per_page = $request->per_page;
        }

        # sort by
        if ($sort_by == 'new') {
            $products = $products->latest();
        } else {
            $products = $products->orderBy('total_sale_count', 'DESC');
        }

        # by price
        if ($request->min_price != null) {
            $min_value = $request->min_price;
        }
        if ($request->max_price != null) {
            $max_value = $request->max_price;
        }
        if ($request->min_price || $request->max_price) {
            $products = $products->where('min_price', '>=', $min_value)->where('min_price', '<=', $max_value);
        }

        # by location
        // if(request()->hasHeader('Stock-Location-Id')){
        //     $product_variations_ids = ProductVariationStock::where('location_id', $request->header('Stock-Location-Id'))->where('stock_qty', '>', 0)->pluck('product_variation_id');
        //     $product_ids = ProductVariation::whereIn('id', $product_variations_ids)->pluck('product_id');
        //     $products = $products->whereIn('id', $product_ids);
        // }
        
        if ($request->location_id && $request->location_id != null) {
            $product_variations_ids = ProductVariationStock::where('location_id', $request->location_id)->where('stock_qty', '>', 0)->pluck('product_variation_id');
            $product_ids = ProductVariation::whereIn('id', $product_variations_ids)->pluck('product_id');
            $products = $products->whereIn('id', $product_ids);
        }

        # by category
        if ($request->category_id && $request->category_id != null) {
            $product_category_product_ids = ProductCategory::where('category_id', $request->category_id)->pluck('product_id');
            $products = $products->whereIn('id', $product_category_product_ids);
        }

        # by tag
        if ($request->tag_id && $request->tag_id != null) {
            $product_tag_product_ids = ProductTag::where('tag_id', $request->tag_id)->pluck('product_id');
            $products = $products->whereIn('id', $product_tag_product_ids);
        }
        # conditional

        $products = $products->paginate(paginationNumber($per_page));
        return  ProductMiniResource::collection($products);
    }


    public function featured()
    {
        $featured_products = getSetting('featured_products_left') != null ? json_decode(getSetting('featured_products_left')) : [];
        $featured_products[] = getSetting('featured_products_right') != null ? json_decode(getSetting('featured_products_right')) : [];

        $products = Product::whereIn('id', $featured_products)->get();

        return ProductMiniResource::collection($products);
    }
    public function trendingProducts()
    {

        $trending_products = getSetting('top_trending_products') != null ? json_decode(getSetting('top_trending_products')) : [];
        $products = Product::whereIn('id', $trending_products)->get();



        return ProductMiniResource::collection($products);
    }

    public function campaignProducts()
    {
        $campaignProductIds = \App\Models\CampaignProduct::pluck('product_id');
        $products = \App\Models\Product::whereIn('id', $campaignProductIds)->paginate(10);
            // ->latest()
            // ->get();
        return ProductMiniResource::collection($products);
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
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->first();

        if (auth()->check() && auth()->user()->user_type == "admin") {
            // do nothing
        } else {

            // if ($product->is_published == 0) {
            //     $product = new Product();
            // }
        }

        return new ProductDetailsResource($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function relatedProducts(Request $request)
    {
        $product = Product::where('slug', $request->slug)->first();
        $productCategories              = $product->categories()->pluck('category_id');
        $productIdsWithTheseCategories  = ProductCategory::whereIn('category_id', $productCategories)->where('product_id', '!=', $product->id)->pluck('product_id');

        $relatedProducts                = Product::whereIn('id', $productIdsWithTheseCategories)->get();

        return ProductMiniResource::collection($relatedProducts);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function productPageWidgets()
    {
        $product_page_widgets = [];
        if (getSetting('product_page_widgets') != null) {
            $product_page_widgets = json_decode(getSetting('product_page_widgets'));
            foreach($product_page_widgets as &$itm){
                $itm->image = $itm->image ? uploadedAsset($itm->image) : '';
            }
        }
        return response()->json($product_page_widgets);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bestSelling(Request $request, $category_id = 0)
    {
        $locations = Location::where('is_published', 1)->where('city_id', $request->city_id)->pluck('id');
        $product_variation_stock_locations = ProductVariationStock::whereIn('location_id', $locations)->pluck('product_variation_id');
        $product_variation_id = ProductVariation::whereIn('id', $product_variation_stock_locations)->pluck('product_id');

        //$best_selling_products = getSetting('best_selling_products') != null ? json_decode(getSetting('best_selling_products')) : [];
        $products = Product::whereIn('id', $product_variation_id)->get();

        return ProductMiniResource::collection($products);
    }


    public function FunctionName(Request $request)
    {

    }
}
