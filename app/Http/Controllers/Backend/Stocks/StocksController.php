<?php

namespace App\Http\Controllers\Backend\Stocks;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariationStock;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StocksController extends Controller
{

    # construct
    public function __construct()
    {
        $this->middleware(['permission:add_stock'])->only(['create', 'store']);
    }

    # add stock form
    public function create()
    {
        $userLocationId = auth()->user()->location_id;
        $isAdmin = auth()->user()->user_type == 'admin';
        $products = Product::latest()->where('is_published', 1)->get();
        if ($isAdmin) {
            $locations = Location::latest()->where('is_published', 1)->get();
        } else {
            // Filter only by user location
            $locations = Location::where('id', $userLocationId)->where('is_published', 1)->get();
        }
        return view('backend.pages.stocks.create', compact('products', 'locations'));
    }
    /**
     * Display a paginated list of product variation stocks along with available locations.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $searchName = $request->input('name');
        $locationId = $request->input('location_id');
        $userLocationId = auth()->user()->location_id;
        $isAdmin = auth()->user()->user_type == 'admin';
    
        if ($isAdmin) {
            $locations = Location::latest()->where('is_published', 1)->get();
            $variations = ProductVariation::with(['product'])->get();
        } else {
            // Filter only by user location
            $locations = Location::where('id', $userLocationId)->where('is_published', 1)->get();
            $variations = ProductVariation::with(['product'])->get(); // Obtener todas las variaciones
        }
    
        $stocksArray = [];
    
        foreach ($variations as $variation) {
            foreach ($locations as $location) {
                $stock = $variation->product_variation_stock_index
                    ->where('location_id', $location->id)
                    ->first();
    
                $data = [
                    'variation' => $variation,
                    'location' => $location,
                    'stock_qty' => $stock ? $stock->stock_qty : 0, // Temporary stock if it does not exist
                    'stock_id' => $stock ? $stock->id : null,
                ];
    
                
                // Apply search by product name
                if (!empty($searchName) && stripos($variation->product->name, $searchName) === false) {
                    continue;
                }
    
                
                // Apply filter by location
                if ($isAdmin && !empty($locationId) && $locationId != $location->id) {
                    continue;
                }
    
                if (!$isAdmin && $location->id != $userLocationId) {
                    continue;
                }
    
                $stocksArray[] = $data;
            }
        }
    
        // Manual pagination
        $perPage = 10;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
    
        $stocks = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($stocksArray, $offset, $perPage),
            count($stocksArray),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    
        return view('backend.pages.stocks.index', compact('stocks', 'locations', 'searchName', 'locationId'));
    }
    

    public function updateStock(Request $request)
    {
        $request->validate([
            'stockId' => 'nullable|exists:product_variation_stocks,id',
            'stock' => 'required|integer|min:0',
        ]);

        // If no stockId is passed, we create a new record
        if ($request->stockId) {
            // If the stockId exists, it is updated
            $stock = ProductVariationStock::findOrFail($request->stockId);
        } else {
            // If a stockId is not passed, we create it
            $stock = new ProductVariationStock();
            // You can assign other values ​​here according to your needs
            $stock->product_variation_id = $request->input('product_variation_id');
            $stock->location_id = $request->input('location_id');
        }
        // We update the stock
        $stock->stock_qty = $request->stock;
        $stock->save();

        flash(localize('Stock updated successfully'))->success();
        return redirect()->route('admin.stocks.index');
    }



    # get variation stock
    public function getVariationStocks(Request $request)
    {
        $product = Product::findOrFail((int) $request->product_id);
        $location_id = $request->location_id;
        return [
            'success'   => true,
            'variation_stocks'  => view('backend.pages.stocks.variation_stocks', compact('product', 'location_id'))->render()
        ];
    }

    # add stock
    public function store(Request $request)
    {
        if ($request->has('product_variation_id')) {
            $productVariationStock = ProductVariationStock::where('product_variation_id', $request->product_variation_id)
                ->where('location_id', $request->location_id)->first();
            if (is_null($productVariationStock)) {
                $productVariationStock = new ProductVariationStock;
                $productVariationStock->product_variation_id = $request->product_variation_id;
                $productVariationStock->location_id = $request->location_id;
            }
            $productVariationStock->stock_qty = $request->stock;
            $productVariationStock->save();
        } else {
            foreach ($request->variationsIds as $key => $productVariationId) {
                $productVariationStock = ProductVariationStock::where('product_variation_id', $productVariationId)
                    ->where('location_id', $request->location_id)->first();

                if (is_null($productVariationStock)) {
                    $productVariationStock = new ProductVariationStock;
                    $productVariationStock->product_variation_id = $productVariationId;
                    $productVariationStock->location_id = $request->location_id;
                }
                $productVariationStock->stock_qty = $request->variationStocks[$key];
                $productVariationStock->save();
            }
        }
        flash(localize('Stock updated successfully'))->success();
        return back();
    }
}
