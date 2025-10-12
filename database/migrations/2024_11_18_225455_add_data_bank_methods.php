<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataBankMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('methods_payments', function (Blueprint $table) {
            $table->string('telefono', 20)->nullable();
            $table->string('rif', 50)->nullable();
            $table->string('banco', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('methods_payments', function (Blueprint $table) {
            //
        });
    }
}
