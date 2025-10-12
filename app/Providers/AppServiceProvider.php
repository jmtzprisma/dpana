<?php

namespace App\Providers;

use App\Models\Theme; 
use Illuminate\Pagination\Paginator; 
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        Schema::defaultStringLength(191); 
            
        if(Schema::hasTable('themes')){ 
            $themes = Theme::where('is_active', 1)->oldest()->get();
            view()->share('themes', $themes);
        }   
    }
}
