<?php

namespace App\Http\Controllers\Api;

use App\Models\Logistic;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/





Route::middleware(['auth:sanctum','isBanned'])->group( function () {
        Route::get("logout",[AuthController::class,"logout"]);

        Route::controller(UserController::class)->group(function(){
            Route::get('customer-profile','index');
            Route::post('customer-profile/update','update');
            Route::post('customer-profile/change-password','passwordUpdate');
        });

        Route::controller(AddressController::class)->group(function(){
            Route::get('address','index');
            Route::get('address/countries','getCountries');
            Route::get('address/states','getStates');
            Route::get('address/cities','getCities');
            Route::get('address/cities_by_country','getCitiesByCountry');
            Route::post('address/store','store');
            Route::get('address/edit/{id}','edit');
            Route::post('address/update','update');
            Route::get('address/delete/{id}','delete');
        });
        Route::post('/address/update-location', [AddressController::class, 'updateLocation'])->name('address.update_location');
    
        Route::controller(CartController::class)->group(function(){
            Route::get("carts","index");
            Route::post("carts/add","store");
            Route::post("carts/update","update");
            Route::post("carts/clear","clearCart");
        });
        Route::controller(CouponController::class)->group(function(){
            Route::get("coupons","index");
            Route::post("coupons/coupon-apply","applyCoupon");
            Route::get("coupons/coupon-remove","clearCoupon");
        });
        Route::controller(OrderController::class)->group(function(){
            Route::get("customer-order-history","index");
            Route::get("order/invoice/{code}","invoice");
            Route::post("order/summery","summery");
            Route::post("order/cod","orderByCOD");
            Route::post("order/cuotas","orderByCuotas");
            Route::post("order/wallet","orderByWallet");
            Route::get("order/online-payment","onlinePay");
            Route::post("order/store","store");
            Route::get("order/track-order","track");
            
            Route::post("order/inlocation/{id}","inLocation");
            Route::post("save-payment", "savePayment");
        });
        Route::controller(WishlistController::class)->group(function(){
            Route::get("/wishlist","index");
            Route::get("/wishlist/check/{id}","check");
            Route::post("/add-to-wishlist","store");
            Route::get("/delete-wishlist/{id}","delete");
        });

        Route::controller(WalletController::class)->group(function(){
            Route::get("/wallet-histories","index");
        });
        Route::controller(RefundController::class)->group(function(){
            Route::get("/refunds","index");
            Route::post("/refund/request","store");
        });

        Route::controller(AuthController::class)->group(function(){
            Route::post("save-location","saveLocation");
        });

        Route::controller(LocationController::class)->group(function(){
            Route::get('locations',"index");
            Route::get('location/{id}',"getDetail");
            Route::get('companies/{category_id}/{subcategory_id?}',"listCompanies");
            Route::get('waiting_list/{location_id}',"saveWaitingList");
        });
});

//end auth middleware


Route::controller(AuthController::class)->group(function(){
    Route::post("valida-image","validaImage")->name('valida_image');
    Route::post("register","register")->name('register');
    Route::post("login","login")->name('login')->middleware('isBanned');
    Route::get("token-check","checkToken")->name('token-check')->middleware('isBanned');

    Route::post("send-code-verify-mobile","sendCodeVerifyMobile")->name('send_code_verify_mobile')->middleware('isBanned');
    Route::post("send-code-verify-email","sendCodeVerifyEmail")->name('send_code_verify_email')->middleware('isBanned');
    Route::post("send-code-recovery-email","sendCodeRecoveryEmail")->name('send_code_recovery_email')->middleware('isBanned');

    Route::post('reset-password', 'updatePw')->name('forgotPw.update')->middleware('isBanned');

    Route::post("verify-is-dni","verifyImageIsDni");
    Route::post("compare-faces","compareFacesAws");
    Route::get("scan-images","scanImages");
    
});

Route::controller(ProductController::class)->group(function(){
    Route::get('products',"index");
    Route::get('products/featured',"featured");
    Route::get('products/trending-products',"trendingProducts");
    Route::post('products/related',"relatedProducts");
    Route::get('products/best-selling/{category_id?}',"bestSelling");
    Route::get('products/widgets',"productPageWidgets");
    Route::get('products/variation-details',"variationDetails");
    Route::get('products/campaign',"campaignProducts");
    Route::get('products/{slug}',"show");
    // Route::post('products/show-product-info',"trendingProducts");
});

Route::controller(CategoryController::class)->group(function(){
    Route::get('category/all',"index");
    Route::get('category/top-category',"topCategory");
    Route::get('category/category-home/{parent_id}',"categoryHome");
});
// Route::controller(LocationController::class)->group(function(){
//     Route::get('locations',"index");
// });

Route::controller(CurrencyController::class)->group(function(){
    Route::get('currencies',"index");
});
Route::controller(LogisticController::class)->group(function(){
    Route::post('logistic-by-city',"getLogistic");
});

Route::controller(BannerController::class)->group(function(){
    Route::get('banner/home',"homeBanner");
   // Route::get('banner/second',"secondBanner");
});
Route::controller(ScheduleDeliveryTimeController::class)->group(function(){
    Route::get('time-slot',"index");
   // Route::get('banner/second',"secondBanner");
});
Route::controller(PaymentTypesController::class)->group(function(){
    Route::get('payment-types',"index");
    Route::get('method-payment-types',"getMethods");
   // Route::get('banner/second',"secondBanner");
});

Route::controller(SettingController::class)->group(function(){
    Route::get('settings',"index");
    Route::get('settings/help-center',"contactInfo");
    Route::get('settings/tipo-cambio',"tipoCambio");
});
Route::controller(PageController::class)->group(function(){
    Route::get('pages/{slug}',"index");
});
Route::controller(LanguageController::class)->group(function(){
    Route::get('languages',"index");
});


// Route::controller(OrderController::class)->group(function(){
//     Route::get("order/online-payment","onlinePay");
    
// });
