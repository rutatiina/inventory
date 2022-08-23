<?php

namespace Rutatiina\Inventory\Services;

use Illuminate\Support\Facades\Auth;
use Rutatiina\Inventory\Models\InventoryPurchase;

trait InventoryPurchaseService
{

    //get the inventory details of a single item or all items
    private function get($item)
    {
        /*
            We use credit account for `accountCode` because we only need to read purchases when inventory account is being credited / issued
        */
        $query = InventoryPurchase::where('financial_account_code', $this->txn['credit_financial_account_code'])
            ->where('item_id', $item['type_id'])
            ->where('currency', $this->txn['base_currency'])
            ->where('date', '<=', $this->txn['date'])
            ->where('balance', '>', 0)
            ->orderBy('date', 'ASC')
            ->get();


        if ($query->isNotEmpty()) {

            $user = auth()->user();

            $purchases = $query->toArray();
            //print_r($purchases); exit;

            #Evaluate Cost based on Inventory Cost method
            if (strtoupper($user->tenant->inventory_valuation_method) == 'FIFO') {
                foreach($purchases as $key => $purchase) {
                    $purchases[$key]['cost'] = $purchase['value_per_unit'];
                }
            }

            elseif (strtoupper($user->tenant->inventory_valuation_method) == 'LIFO') {
                //Reverse the array since purchases are read from db in FIFO order i.e. date ASC
                $purchases = array_reverse($purchases);

                foreach($purchases as $key => $purchase) {
                    $purchases[$key]['cost'] = $purchase['value_per_unit'];
                }
            }

            elseif (strtoupper($user->tenant->inventory_valuation_method) == 'AVCO') {

                $total_purchased_units = 0;
                $total_purchased_value = 0;

                foreach($purchases as $Key => $purchase)
                {
                    $total_purchased_units += $purchase['units'];
                    $total_purchased_value += $purchase['units'] * $purchase['balance'];
                }

                $average_cost = $total_purchased_value / $total_purchased_units;

                foreach($purchases as $key => $purchase)
                {
                    $purchases[$key]['cost'] = $average_cost;
                }
            }

            #Actual Unit Cost Method
            elseif (strtoupper($user->tenant->inventory_valuation_method) == 'AUCO') {

                foreach($purchases as $key => $purchase) {
                    $purchases[$key]['cost'] = $item['cost'] / $item['units'];
                }
            }

            #End of inventory valuation calculation

            return $purchases;

        } else {
            return false;
        }
    }
    
    //update the invetory purchase details of the items
    public static function update($txn, $reverse = false)
    {
        if (strtolower($txn['status']) == 'draft')
        {
            //cannot update balances for drafts
            return false;
        }

        if (isset($txn['balances_where_updated']) && $txn['balances_where_updated'])
        {
            //cannot update balances for task already completed
            return false;
        }

        $sign = 1;

        if ($reverse)
        {
            $sign = -1;
        }

        $items = $this->inventoryTrackableItems();

        if (empty($items)) 
        {
            $this->errors[] = 'Inventory Purchase Error: Item(s) are not product or inventory tracking is not enabled.';
            return false;
        }

        foreach($items as $item) 
        {
            //$item_id = (empty($item['parent_id']))? $item['id'] : $item['parent_id']; //Set at item selection

            $item['units'] = (empty($item['units'])) ? 1 : $item['units'];
            $units = $item['units'] * $item['quantity'];

            //NOTE:: Units & balances fields is only set on purchases
            // Units fields NEVER edited/updated
            // balances fields is whats updated cz it shows the units available
            $InventoryPurchase = new InventoryPurchase;
            $InventoryPurchase->tenant_id = Auth::user()->tenant->id;
            $InventoryPurchase->date = $txn['date'];
            $InventoryPurchase->item_id = $item['type_id'];
            $InventoryPurchase->batch = $item['batch'];
            $InventoryPurchase->units = ($sign * $units);
            $InventoryPurchase->save();

        }

        return true;

    }



}
