<?php

namespace App\Http\Controllers;

use Database\Seeders\DeliverymanPermissionSeeder;
use DB;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends Controller
{
    # init update 
    public function init()
    {
        return view('update.init');
    }

    # update complete
    public function complete()
    {
        // v1.0.0 || v1.5.0 || v1.5.1 --> v2.0.0
        if (env('APP_VERSION') == 'v1.0.0' || env('APP_VERSION') == 'v1.5.0' || env('APP_VERSION') == 'v1.5.1') {
            try {
                #v2.0.0
                $sql_path = base_path('alterQueries/v200.sql');
                DB::unprepared(file_get_contents($sql_path));

                #v2.5.0 
                $sql_path = base_path('alterQueries/v250.sql');
                DB::unprepared(file_get_contents($sql_path));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        // v2.0.0 || v2.1.0 || v2.5.0 --> v2.5.1
        if (env('APP_VERSION') == 'v2.0.0' || env('APP_VERSION') == 'v2.1.0' || env('APP_VERSION') == 'v2.5.0') {
            try {
                $sql_path = base_path('alterQueries/v250.sql');
                DB::unprepared(file_get_contents($sql_path));
            } catch (\Throwable $th) {
                //throw $th;
            }
        } 

        try {  
            Artisan::call('migrate');

            $seeder  = new DeliverymanPermissionSeeder();

            $seeder->run();
            
        } catch (\Throwable $th) {
            //throw $th;
        }

        # latest version
        writeToEnvFile('APP_VERSION', 'v2.7.0');
         
        
        if(!empty(env('RECAPTCHA_SITE_KEY'))){ 
            writeToEnvFile('RECAPTCHAV3_SITEKEY', env('RECAPTCHA_SITE_KEY')); 
        }

        if(!empty(env('RECAPTCHA_SECRET_KEY'))){ 
            writeToEnvFile('RECAPTCHAV3_SECRET', env('RECAPTCHA_SECRET_KEY')); 
        } 

        cacheClear();
        $oldRouteServiceProvider        = base_path('app/Providers/RouteServiceProvider.php');
        $setupRouteServiceProvider      = base_path('app/Providers/SetupServiceComplete.php');
        copy($setupRouteServiceProvider, $oldRouteServiceProvider);
        return view('update.complete');
    }
}
