<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('banner')->nullable();

            $table->foreignIdFor(App\Models\Category::class)
                ->references('id')->on('categories')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id');
            // $table->foreignIdFor(App\Models\Company::class)
            //     ->references('id')->on('companies')->default(1)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
