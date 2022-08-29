<?php

namespace Rutatiina\Inventory\Http\Controllers;

use PDF;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Rutatiina\Item\Models\Item;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Rutatiina\Inventory\Models\Inventory;

use Rutatiina\Contact\Traits\ContactTrait;
use Rutatiina\GoodsReceived\Models\GoodsReceived;
use Rutatiina\GoodsReturned\Models\GoodsReturned;
use Rutatiina\GoodsDelivered\Models\GoodsDelivered;
use Rutatiina\GoodsReturned\Traits\Item as TxnItem;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Rutatiina\GoodsReturned\Services\GoodsReturnedService;
use Rutatiina\FinancialAccounting\Traits\FinancialAccountingTrait;
use Rutatiina\GoodsReceived\Services\GoodsReceivedInvetoryService;
use Rutatiina\GoodsDelivered\Services\GoodsDeliveredInventoryService;
use Rutatiina\GoodsIssued\Models\GoodsIssued;
use Rutatiina\GoodsIssued\Services\GoodsIssuedInventoryService;
use Rutatiina\GoodsReturned\Services\GoodsReturnedInventoryService;

class InventoryItemController extends Controller
{

    public function __construct()
    {
        // $this->middleware('permission:goods-returned.view');
		// $this->middleware('permission:goods-returned.create', ['only' => ['create','store']]);
		// $this->middleware('permission:goods-returned.update', ['only' => ['edit','update']]);
		// $this->middleware('permission:goods-returned.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson()) {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $per_page = ($request->per_page) ? $request->per_page : 50;

        $query = Item::query();

        if ($request->search)
        {
            $query->where(function($q) use ($request) {
                $q->where('type', 'like', '%'.Str::replace(' ', '%', $request->search).'%');
                $q->orWhere('name', 'like', '%'.Str::replace(' ', '%', $request->search).'%');
                $q->orWhere('sku', 'like', '%'.Str::replace(' ', '%', $request->search).'%');
                $q->orWhere('barcode', 'like', '%'.Str::replace(' ', '%', $request->search).'%');
                $q->orWhere('selling_description', 'like', '%'.Str::replace(' ', '%', $request->search).'%');
                $q->orWhere('billing_description', 'like', '%'.Str::replace(' ', '%', $request->search).'%');
            });
        }

        if ($request->items_with_inventory == 'true')
        {
            // $query->has('inventory_records');
            $query->whereHas('inventory_records', function ($query) {
                return $query->where('units_received', '<>', 0)
                    ->orWhere('units_delivered', '<>', 0)
                    ->orWhere('units_issued', '<>', 0)
                    ->orWhere('units_returned', '<>', 0)
                    ->orWhere('units_available', '<>', 0);
            });
        }

        $query->orderBy('name', 'asc');
        $txns = $query->paginate($per_page);

        $txns->append('inventory');

        return [
            'tableData' => $txns,
        ];
    }

    public function create()
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request)
    {
        //
    }

    public function destroy($id)
	{
        //
    }

	#-----------------------------------------------------------------------------------

    public function recompute()
    {

        //delete all previous inventory data
        Inventory::where('id', '>', 0)->delete();

        //Update items received
        $goodsReceived = GoodsReceived::with('items')->get();
        //return $goodsReceived->first()->toArray();
        foreach($goodsReceived as $t)
        {
            $t->items->map(function ($item, $key) {
                $item->inventory_tracking = ($item->item) ? $item->item->inventory_tracking : 0;
                return $item;
            });
            unset($item);

            GoodsReceivedInvetoryService::update($t->toArray());
        }

        //Update items delivered
        $GoodsDelivered = GoodsDelivered::get();
        foreach($GoodsDelivered as $t)
        {
            // $_t = $t->toArray();
            // foreach($_t['items'] as &$item) 
            // {
            //     $_itemModel = Item::find($item['item_id']);
            //     $item['inventory_tracking'] = ($_itemModel) ? $_itemModel->inventory_tracking : 0;
            //     $item['units'] = (is_numeric($item['units'])) ? $item['units'] : 0;
            // };
            // unset($item);
            
            GoodsDeliveredInventoryService::update($t);
        }
        

        //update items issued
        $GoodsIssued = GoodsIssued::with('items')->get();
        foreach($GoodsIssued as $t)
        {
            $t->items->map(function ($item, $key) {
                $item->inventory_tracking = ($item->item) ? $item->item->inventory_tracking : 0;
                return $item;
            });
            unset($item);

            GoodsIssuedInventoryService::update($t->toArray());
        }

        //update items returned
        $GoodsReturned = GoodsReturned::with('items')->get();
        foreach($GoodsReturned as $t)
        {
            $t->items->map(function ($item, $key) {
                $item->inventory_tracking = ($item->item) ? $item->item->inventory_tracking : 0;
                return $item;
            });
            unset($item);

            GoodsReturnedInventoryService::update($t->toArray());
        }

        return [
            'status' => true,
            'messages' => ['Inventory recomputing complete'],
        ];
    }

    

}
