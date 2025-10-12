<?php

namespace App\Http\Controllers\Backend\Reports;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Str;
use DB;

class ReportsController extends Controller
{
    # construct
    public function __construct()
    {
        $this->middleware(['permission:product_sale_reports'])->only('index');
        $this->middleware(['permission:order_reports'])->only('orders');
        $this->middleware(['permission:category_sale_reports'])->only('categoryWise');
        $this->middleware(['permission:sales_amount_reports'])->only('amountWise');
        $this->middleware(['permission:delivery_status_reports'])->only('deliveryStatus');
    }

    # product sales
    public function index(Request $request)
    {
        $searchKey  = null;
        $order = 'DESC';
    
        if ($request->order == "ASC") {
            $order = 'ASC';
        }
    
        // Get only the products sold in the user's location
        $products = Product::whereHas('variations.orderItems.order', function ($query) {
            $query->byLocation(); // Filter by the authenticated user's location
        })
        ->orderBy('total_sale_count', $order);
    
        // Search filter
        if ($request->search != null) {
            $products = $products->where('name', 'like', '%' . $request->search . '%');
            $searchKey = $request->search;
        }
    
        $products = $products->paginate(paginationNumber(30), ['name', 'thumbnail_image', 'slug', 'total_sale_count']);
    
        return view('backend.pages.reports.sales', compact('products', 'order', 'searchKey'));
    }
    


    # orders
    public function orders(Request $request)
    {
        $searchCode = null;
        $deliveryStatus = null;
        $paymentStatus = null;

        $orders = Order::latest()->byLocation();

        # conditional
        if ($request->delivery_status != null) {
            $deliveryStatus = $request->delivery_status;
            $orders = $orders->where('delivery_status', $deliveryStatus);
        }

        if ($request->payment_status != null) {
            $paymentStatus = $request->payment_status;
            $orders = $orders->where('payment_status', $paymentStatus);
        }

        if (Str::contains($request->date_range, 'to') && $request->date_range != null) {
            $date_var = explode(" to ", $request->date_range);
        } else {
            $date_var = [date("d-m-Y", strtotime('7 days ago')), date("d-m-Y", strtotime('today'))];
        }

        $orders = $orders->where('created_at', '>=', date("Y-m-d", strtotime($date_var[0])))->where('created_at', '<=',  date("Y-m-d", strtotime($date_var[1]) + 86400000));

        $orderGroupIds = $orders->pluck('order_group_id');
        $totalAmount = OrderGroup::whereIn('id', $orderGroupIds)->sum('grand_total_amount');
        $orders = $orders->paginate(paginationNumber());
        return view('backend.pages.reports.orders', compact('orders', 'deliveryStatus', 'paymentStatus', 'date_var', 'totalAmount'));
    }

    # categoryWise sales
    public function categoryWise(Request $request)
    {
        $searchKey = null;
        $order = $request->order === "ASC" ? "ASC" : "DESC";

        $categories = Category::withCount(['products as total_sale_count' => function ($query) {
            $query->whereHas('variations.orderItems.order', function ($orderQuery) {
                $orderQuery->byLocation(); 
            });
        }])->orderBy('total_sale_count', $order);

        if ($request->search) {
            $categories->where('name', 'like', '%' . $request->search . '%');
            $searchKey = $request->search;
        }

        $categories = $categories->paginate(paginationNumber(30));

        return view('backend.pages.reports.categorySales', compact('categories', 'order', 'searchKey'));
    }



    # salesAmount wise
    public function amountWise(Request $request)
    {
        $order = 'DESC';

        if (Str::contains($request->date_range, 'to') && $request->date_range != null) {
            $date_var = explode(" to ", $request->date_range);
        } else {
            $date_var = [date("d-m-Y", strtotime('7 days ago')), date("d-m-Y", strtotime('today'))];
        }

        if ($request->order == "ASC") {
            $order = 'ASC';
        }

        $orderItemsQuery = OrderItem::whereHas('order', function ($query) {
            $query->byLocation(); 
        })
        ->whereBetween('created_at', [
            date("Y-m-d", strtotime($date_var[0])),
            date("Y-m-d", strtotime($date_var[1]) + 86400)
        ])
        ->orderBy('total_price', $order);

        $totalPrice = $orderItemsQuery->sum('total_price');
        $orderItems = $orderItemsQuery
            ->groupBy('created_at')
            ->selectRaw('created_at, sum(total_price) as total_price')
            ->paginate(paginationNumber(30));

        return view('backend.pages.reports.amountWise', compact('orderItems', 'totalPrice', 'order', 'date_var'));
    }


    # deliveryStatusWise
    public function deliveryStatus(Request $request)
    {
        if (Str::contains($request->date_range, 'to') && $request->date_range != null) {
            $date_var = explode(" to ", $request->date_range);
        } else {
            $date_var = [date("d-m-Y", strtotime('7 days ago')), date("d-m-Y", strtotime('today'))];
        }

        $orderQuery = Order::byLocation() // Filter by location if the user is not admin
            ->where('created_at', '>=', date("Y-m-d", strtotime($date_var[0])))
            ->where('created_at', '<=', date("Y-m-d", strtotime($date_var[1]) + 86400000));

        $totalOrders = $orderQuery->count();
        $orders = $orderQuery->groupBy('delivery_status')->selectRaw('delivery_status, count(delivery_status) as total_order')->paginate(paginationNumber(30));

        return view('backend.pages.reports.deliveryStatus', compact('orders', 'totalOrders', 'date_var'));
    }

}
