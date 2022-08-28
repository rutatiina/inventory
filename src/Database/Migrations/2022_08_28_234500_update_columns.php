<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->table('rg_inventory', function (Blueprint $table) {
            $table->bigInteger('units_received')->change();
            $table->bigInteger('units_delivered')->change();
            $table->bigInteger('units_issued')->change();
            $table->bigInteger('units_returned')->change();
            $table->bigInteger('units_available')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //do nothing
    }
}
