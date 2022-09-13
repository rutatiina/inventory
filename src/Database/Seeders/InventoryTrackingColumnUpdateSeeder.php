<?php

namespace Rutatiina\Inventory\Database\Seeders;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Rutatiina\Item\Models\Item;

//php artisan db:seed --class=Rutatiina\\Inventory\\Database\\Seeders\\InventoryTrackingColumnUpdateSeeder
class InventoryTrackingColumnUpdateSeeder extends Seeder
{
   /**
    * Run the database seeders.
    *
    * @return void
    */
   public function run()
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

      foreach ($tables as $key => $table) 
      {
         $items = DB::connection('tenant')->table($table)->get();

         foreach ($items as $k => $item) 
         {
            $itemModel = Item::withoutGlobalScopes()->where('id', $item->item_id)->first();

            if ($itemModel && $itemModel->inventory_tracking) 
            {
               DB::connection('tenant')->table($table)->where('id', $item->id)->update([
                  'inventory_tracking' => 1
               ]);
               $this->command->line($table.'/'.$item->id . ' - ' . $item->inventory_tracking);
            }
         }
      }
    }
}
