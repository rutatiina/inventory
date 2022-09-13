<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvetoryTrackingColumnToItemsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'rg_goods_delivered_items',
            'rg_goods_issued_items',
            'rg_goods_received_items',
            'rg_goods_returned_items',
            'rg_sales_items',
            'rg_invoice_items',
            'rg_pos_order_items',
        ];

        foreach($tables as $t)
        {
            if (!Schema::connection('tenant')->hasColumn($t, 'inventory_tracking')) 
            {
                Schema::connection('tenant')->table($t, function (Blueprint $table) 
                {
                    $table->unsignedTinyInteger('inventory_tracking')->default(0)->nullable();
                });
            }
        
        }
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
