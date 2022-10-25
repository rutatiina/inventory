<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialAccountCodeColumnsToRgInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->table('rg_inventory', function (Blueprint $table) {
            $table->unsignedInteger('financial_account_code')->after('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->table('rg_inventory', function (Blueprint $table) {
            $table->removeColumn('financial_account_code');
        });
    }
}
