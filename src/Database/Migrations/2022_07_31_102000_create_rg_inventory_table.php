<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRgInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('rg_inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            //>> default columns
            $table->softDeletes();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            //<< default columns

            //>> table columns
            $table->unsignedBigInteger('project_id')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('item_id');
            $table->char('batch', 100)->nullable();

            $table->unsignedBigInteger('units_received')->default(0);
            $table->unsignedBigInteger('units_delivered')->default(0);
            $table->unsignedBigInteger('units_issued')->default(0);
            $table->unsignedBigInteger('units_returned')->default(0);
            $table->unsignedBigInteger('units_available')->default(0);


            //indexing
            $table->unique([
                'tenant_id', 
                'project_id',
                'date',
                'item_id',
                'batch',
            ], 'unique_parameters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('rg_inventory');
    }
}
