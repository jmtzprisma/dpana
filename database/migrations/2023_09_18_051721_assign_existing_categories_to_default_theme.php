<?php

use App\Models\Category;
use App\Models\CategoryTheme;
use App\Scopes\ThemeCampaignScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AssignExistingCategoriesToDefaultTheme extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('themes') && Schema::hasTable('categories')){ 
            $categories = Category::withoutGlobalScope(ThemeCategoryScope::class)->get(); 
            $data = [];
            foreach ($categories as $category) {
                $tempArr = [
                    'category_id' => $category->id,
                    'theme_id' => 1,
                ];
                array_push($data, $tempArr);
            }  
            if(!empty($data)){
                CategoryTheme::insert($data);
            }
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
