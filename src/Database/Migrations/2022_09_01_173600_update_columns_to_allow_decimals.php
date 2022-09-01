<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsToAllowDecimals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->table('rg_inventory', function (Blueprint $table) {
            $table->decimal('units_received', 61, 4)->default(0)->change();
            $table->decimal('units_delivered', 61, 4)->default(0)->change();
            $table->decimal('units_issued', 61, 4)->default(0)->change();
            $table->decimal('units_returned', 61, 4)->default(0)->change();
            $table->decimal('units_available', 61, 4)->default(0)->change();
        });

        $tablesQuantity = [
            'rg_bill_items',
            'rg_bill_recurring_bill_items',
            'rg_cash_sale_items',
            'rg_credit_note_items',
            'rg_debit_note_items',
            'rg_estimate_items',
            'rg_goods_delivered_items',
            'rg_goods_issued_items',
            'rg_goods_received_items',
            'rg_goods_returned_items',
            'rg_invoice_items',
            'rg_item_sub_items',
            'rg_pos_order_items',
            'rg_purchase_order_items',
            'rg_recurring_invoice_items',
            'rg_retainer_invoice_items',
            'rg_sales_order_items',
        ];

        foreach($tablesQuantity as $t)
        {
            Schema::connection('tenant')->table($t, function (Blueprint $table) {
                $table->decimal('quantity', 61, 4)->default(0)->change();
            });
        }

        $tablesAmount = [
            'rg_expense_items',
            'rg_expense_recurring_expense_items',
            'rg_payment_received_items',
            'rg_payments_made_items',
            'rg_receipt_items',
        ];

        foreach($tablesAmount as $t)
        {
            Schema::connection('tenant')->table($t, function (Blueprint $table) {
                $table->decimal('amount', 61, 4)->default(0)->change();
            });
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
