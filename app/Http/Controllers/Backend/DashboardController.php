<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    # admin dashboard
    public function index(Request $request)
    {
        # total sales chart 
        $totalSalesChart = $this->totalSalesChart($request->timeline);
        $totalSalesData  = $totalSalesChart[0];
        $timelineText    = $totalSalesChart[1];

        # top 5 category sales  
        $totalCatSalesData = $this->topFiveCategoryChart();

        # last 30 days orders  
        $totalOrdersData = $this->last30DaysOrderChart();

        # this month sales  
        $thisMonthSaleData  = $this->thisMonthSaleChart();

        # --------------------------------------------------------------counters
        $isAdmin = auth()->user()->user_type == 'admin';
        $userLocationId = auth()->user()->location_id;

        $dayStart   = Carbon::now()->startOfDay();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart  = Carbon::now()->startOfYear();

        // ------------------------------
        // Today's Earning
        // ------------------------------
        $todayOrderQuery = Order::where('delivery_status', '!=', orderCancelledStatus())
            ->where('created_at', '>=', $dayStart);

        if (!$isAdmin) {
            $todayOrderQuery->where('location_id', $userLocationId);
        }

        $orderGroupIds = $todayOrderQuery->pluck('order_group_id');
        $todayEarning  = OrderGroup::whereIn('id', $orderGroupIds)->sum('grand_total_amount');

        // ------------------------------
        // Today's Pending Earning
        // ------------------------------
        $todayPendingOrderQuery = Order::where('delivery_status', '!=', orderDeliveredStatus())
            ->where('delivery_status', '!=', orderCancelledStatus())
            ->where('created_at', '>=', $dayStart);

        if (!$isAdmin) {
            $todayPendingOrderQuery->where('location_id', $userLocationId);
        }

        $orderGroupIds       = $todayPendingOrderQuery->pluck('order_group_id');
        $todayPendingEarning = OrderGroup::whereIn('id', $orderGroupIds)->sum('grand_total_amount');

        // ------------------------------
        // This Year Earning
        // ------------------------------
        $yearOrderQuery = Order::where('delivery_status', '!=', orderCancelledStatus())
            ->where('created_at', '>=', $yearStart);

        if (!$isAdmin) {
            $yearOrderQuery->where('location_id', $userLocationId);
        }

        $orderGroupIds  = $yearOrderQuery->pluck('order_group_id');
        $thisYearEarning = OrderGroup::whereIn('id', $orderGroupIds)->sum('grand_total_amount');

        // ------------------------------
        // Total Earning
        // ------------------------------
        $totalOrderQuery = Order::where('delivery_status', '!=', orderCancelledStatus());

        if (!$isAdmin) {
            $totalOrderQuery->where('location_id', $userLocationId);
        }

        $orderGroupIds = $totalOrderQuery->pluck('order_group_id');
        $totalEarning  = OrderGroup::whereIn('id', $orderGroupIds)->sum('grand_total_amount');

        // ------------------------------
        // Total Sales Count (All Time)
        // ------------------------------
        $totalSaleQuery = OrderItem::query(); 

        if (!$isAdmin) {
            $totalSaleQuery->whereHas('location', function ($query) use ($userLocationId) {
                $query->where('id', $userLocationId);
            });
        }

        $totalSaleCount = $totalSaleQuery->sum('qty');

        // ------------------------------
        // Today's Sale Count
        // ------------------------------
        $todaySaleQuery = OrderItem::where('created_at', '>=', $dayStart);
        if (!$isAdmin) {
            $todaySaleQuery->whereHas('location', function ($query) use ($userLocationId) {
                $query->where('id', $userLocationId);
            });
        }
        $todaySaleCount = $todaySaleQuery->sum('qty');

        // ------------------------------
        // This Month Sale Count
        // ------------------------------
        $monthSaleQuery = OrderItem::where('created_at', '>=', $monthStart);
        if (!$isAdmin) {
            $monthSaleQuery->whereHas('location', function ($query) use ($userLocationId) {
                $query->where('id', $userLocationId);
            });
        }
        $monthSaleCount = $monthSaleQuery->sum('qty');

        // ------------------------------
        // This Year Sale Count
        // ------------------------------
        $yearSaleQuery = OrderItem::where('created_at', '>=', $yearStart);
        if (!$isAdmin) {
            $yearSaleQuery->whereHas('location', function ($query) use ($userLocationId) {
                $query->where('id', $userLocationId);
            });
        }
        $yearSaleCount = $yearSaleQuery->sum('qty');

        // ------------------------------
        // Total categories with stock
        // ------------------------------
        $categoryQuery = Category::whereHas('products', function ($query) use ($isAdmin, $userLocationId) {
            $query->whereHas('variations', function ($query) use ($isAdmin, $userLocationId) {
                $query->whereHas('product_variation_stock_index', function ($query) use ($isAdmin, $userLocationId) {
                    if (!$isAdmin) {
                        $query->where('location_id', $userLocationId);
                    }
                });
            });
        });
    
        $totalCategoriesWithStock = $categoryQuery->count();
        


        # --------------------------------------------------------------counters

        $values = [
            'totalSalesData'            => $totalSalesData,
            'timelineText'              => $timelineText,
            'totalCatSalesData'         => $totalCatSalesData,
            'totalOrdersData'           => $totalOrdersData,
            'thisMonthSaleData'         => $thisMonthSaleData,
            'todayEarning'              => $todayEarning,
            'todayPendingEarning'       => $todayPendingEarning,
            'totalEarning'              => $totalEarning,
            'thisYearEarning'           => $thisYearEarning,

            'todaySaleCount'            => $todaySaleCount,
            'monthSaleCount'            => $monthSaleCount,
            'yearSaleCount'             => $yearSaleCount,
            'totalSaleCount'            =>$totalSaleCount,
            'totalCategoriesWithStock'  =>$totalCategoriesWithStock,
        ];

        $view = view('backend.pages.dashboard', $values);

        # give permission to the Super admin
        $user = auth()->user();
        if ($user->user_type == 'admin' && $user->hasRole('Super Admin')) {
            return $view;
        } else if ($user->user_type == 'admin') {
            $user->assignRole('Super Admin');
        }
        return $view;
    }

    # admin profile
    public function profile()
    {
        $user = auth()->user();
        return view('backend.pages.profile', compact('user'));
    }

    # admin profile
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $user->name = $request->name;
        $user->phone = validatePhone($request->phone);
        $user->avatar = $request->avatar;

        if ($request->has('password') && $request->password != '') {
            if ($request->password != $request->password_confirmation) {
                flash(localize('Password confirmation does not match'))->error();
                return back();
            }
            $user->password = Hash::make($request->password);
        }

        $user->save();

        flash(localize('Profile has been updated'))->success();
        return back();
    }

    # total sales chart
    private function totalSalesChart($time)
    {
        $timeline                   = 7; // 7, 30 o 90 días
        $timelineText               = localize('Last 7 days');

        if ((int)$time > 7) {
            $timeline = (int) $time;
            $timelineText = $timeline == 30 ? localize('Last 30 days') : localize('Last 3 months');
        }

        $isAdmin = auth()->user()->user_type == 'admin';
        $userLocationId = auth()->user()->location_id;

        $orderGroupIds = Order::where('delivery_status', '!=', orderCancelledStatus())
            ->where('created_at', '>=', Carbon::now()->subDays($timeline))
            ->pluck('order_group_id');

        // If not admin, filter by location_id
        $orderGroupsQuery = OrderGroup::whereIn('id', $orderGroupIds)
            ->when(!$isAdmin, function ($query) use ($userLocationId) {
                return $query->where('location_id', $userLocationId);
            })
            ->oldest();

        $totalSalesTimelineInString = '';
        $totalSalesAmountInString   = '';

        for ($i = $timeline; $i >= 0; $i--) {
            $totalSalesAmount = 0;

            foreach ($orderGroupsQuery->get() as $orderGroup) {
                if (date('Y-m-d', strtotime($i . ' days ago')) == date('Y-m-d', strtotime($orderGroup->created_at))) {
                    $totalSalesAmount += $orderGroup->grand_total_amount;
                }
            }

            $totalSalesTimelineInString .= json_encode(date('Y-m-d', strtotime($i . ' days ago'))) . ($i == 0 ? '' : ',');
            $totalSalesAmountInString .= json_encode($totalSalesAmount) . ($i == 0 ? '' : ',');
        }

        $totalSalesData = new SystemSetting; // instancia temporal
        $totalSalesData->labels = $totalSalesTimelineInString;
        $totalSalesData->amount = $totalSalesAmountInString;
        $totalSalesData->totalEarning = $orderGroupsQuery->sum('grand_total_amount');

        return [$totalSalesData, $timelineText];
    }


    # top 5 category chart
    private function topFiveCategoryChart()
    {
        $categories = Category::withCount(['products as total_sale_count' => function ($query) {
            $query->whereHas('variations.orderItems.order', function ($orderQuery) {
                $orderQuery->byLocation(); 
            });
        }])->orderBy('total_sale_count', 'DESC')->take(5)->get();

        $totalCategorySalesCount = $categories->sum('total_sale_count');
        $catLabelsInString = '';
        $catSeries = [];

        foreach ($categories as $key => $cat) {
            $catLabelsInString .= json_encode($cat->name);
            if ($key + 1 != count($categories)) {
                $catLabelsInString .= ',';
            }
            array_push($catSeries, (float) $cat->total_sale_count);
        }

        $totalCatSalesData = new SystemSetting; 
        $totalCatSalesData->totalCategorySalesCount = $totalCategorySalesCount;
        $totalCatSalesData->series = json_encode($catSeries);
        $totalCatSalesData->labels = $catLabelsInString;

        return $totalCatSalesData;
    }

    # last 30 days order
    private function last30DaysOrderChart()
    {
        $timelineOrder = 30; // 7, 30 o 90 días   
        $totalOrdersTimelineInString = '';
        $totalOrdersAmountInString = '';

        $isAdmin = auth()->user()->user_type == 'admin';
        $userLocationId = auth()->user()->location_id;

        // Filter by location if user is not admin
        $ordersQuery = Order::where('created_at', '>=', Carbon::now()->subDays($timelineOrder))
            ->when(!$isAdmin, function ($query) use ($userLocationId) {
                return $query->where('location_id', $userLocationId);
            })
            ->oldest();

        for ($j = $timelineOrder; $j >= 0; $j--) {
            $totalOrdersAmount = 0;

            foreach ($ordersQuery->get() as $order) {
                if (date('Y-m-d', strtotime($j . ' days ago')) == date('Y-m-d', strtotime($order->created_at))) {
                    $totalOrdersAmount += 1;
                }
            }

            $totalOrdersTimelineInString .= json_encode(date('Y-m-d', strtotime($j . ' days ago'))) . ($j == 0 ? '' : ',');
            $totalOrdersAmountInString .= json_encode($totalOrdersAmount) . ($j == 0 ? '' : ',');
        }

        $totalOrdersData = new SystemSetting; // instancia temporal
        $totalOrdersData->labels = $totalOrdersTimelineInString;
        $totalOrdersData->amount = $totalOrdersAmountInString;
        $totalOrdersData->totalOrders = $ordersQuery->count();

        return $totalOrdersData;
    }


    # this month sale's chart
    private function thisMonthSaleChart()
    {
        $user = auth()->user();
        $monthStart = Carbon::now()->startOfMonth();
        $ordersQuery = Order::where('delivery_status', '!=', orderCancelledStatus())
                            ->where('created_at', '>=', $monthStart);

        // Filter by location if user is not admin
        if ($user->user_type !== 'admin') {
            $ordersQuery->where('location_id', $user->location_id);
        }
        $orderGroupIds = $ordersQuery->pluck('order_group_id');
        $orderGroupsThisMonthQuery = OrderGroup::whereIn('id', $orderGroupIds)->oldest();

        
        $thisMonthTimelineInString = '';
        $thisMonthAmountInString   = '';
        $today = today();
        $dates = [];
        $datesReadable = [];

        for ($i = 1; $i < $today->daysInMonth + 1; ++$i) {
            $dates[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('Y-m-d');
            $datesReadable[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('d M');
        }

        foreach ($dates as $key => $date) {
            $totalOrdersAmount = 0;
            foreach ($orderGroupsThisMonthQuery->get() as $orderGroup) {
                if ($date == date('Y-m-d', strtotime($orderGroup->created_at))) {
                    $totalOrdersAmount += $orderGroup->grand_total_amount;
                }
            }

            if ($key == count($dates) - 1) {
                $thisMonthTimelineInString .= json_encode($datesReadable[$key]);
                $thisMonthAmountInString .= json_encode($totalOrdersAmount);
            } else {
                $thisMonthTimelineInString .= json_encode($datesReadable[$key]) . ',';
                $thisMonthAmountInString .= json_encode($totalOrdersAmount) . ',';
            }
        }

        $thisMonthSaleData = new SystemSetting; 
        $thisMonthSaleData->labels = $thisMonthTimelineInString;
        $thisMonthSaleData->amount = $thisMonthAmountInString;
        $thisMonthSaleData->totalEarning = $orderGroupsThisMonthQuery->sum('grand_total_amount');

        return $thisMonthSaleData;
    }

}
