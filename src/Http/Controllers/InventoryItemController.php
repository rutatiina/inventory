<?php

namespace Rutatiina\Inventory\Http\Controllers;

use PDF;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Rutatiina\Item\Models\Item;
use Rutatiina\Sales\Models\Sales;
use Illuminate\Support\Facades\DB;
use Rutatiina\POS\Models\POSOrder;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Rutatiina\Sales\Models\SalesItem;
use Rutatiina\POS\Models\POSOrderItem;
use Yajra\DataTables\Facades\DataTables;
use Rutatiina\Inventory\Models\Inventory;
use Rutatiina\Contact\Traits\ContactTrait;
use Rutatiina\GoodsIssued\Models\GoodsIssued;
use Rutatiina\GoodsReceived\Models\GoodsReceived;
use Rutatiina\GoodsReturned\Models\GoodsReturned;
use Rutatiina\GoodsDelivered\Models\GoodsDelivered;
use Rutatiina\GoodsReturned\Traits\Item as TxnItem;
use Rutatiina\GoodsReceived\Models\GoodsReceivedItem;
use Rutatiina\GoodsDelivered\Models\GoodsDeliveredItem;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Rutatiina\GoodsReturned\Services\GoodsReturnedService;
use Rutatiina\GoodsIssued\Services\GoodsIssuedInventoryService;
use Rutatiina\FinancialAccounting\Traits\FinancialAccountingTrait;
use Rutatiina\GoodsReceived\Services\GoodsReceivedInventoryService;
use Rutatiina\GoodsReturned\Services\GoodsReturnedInventoryService;
use Rutatiina\GoodsDelivered\Services\GoodsDeliveredInventoryService;

class InventoryItemController extends Controller
{

    public function __construct()
    {
        // $this->middleware('permission:goods-returned.view');
		// $this->middleware('permission:goods-returned.create', ['only' => ['create','store']]);
		// $this->middleware('permission:goods-returned.update', ['only' => ['edit','update']]);
		// $this->middleware('permission:goods-returned.delete', ['only' => ['destroy']]);
    }

    public function items(Request $request)
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

    public function item(Request $request, $id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson()) {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $per_page = ($request->per_page) ? $request->per_page : 20;

        $txns = GoodsReceived::with(['items' => function ($q) use($id) {
                $q->where('item_id', $id);
            }])
            ->orderBy('id','desc')
            ->paginate($per_page);

        //goods delivered
        $goodsDelivered = DB::connection('tenant')
        ->table('rg_goods_delivered')
        ->select('id', 'tenant_id', 'date', 'number', 'status', 
            DB::raw("'goods_delivered' as content"), 
            DB::raw("'Delivered' as txn_code"),
            DB::raw("'goods-delivered' as link")
        )
        ->where('tenant_id', session('tenant_id'))
        ->where(function($q) {
            $q->whereNotIn('status', ['edited']);
            $q->orWhereNull('status');
        })
        ->whereExists(function($query) use ($id) {
            $query->select(DB::raw(1))
                ->from('rg_goods_delivered_items')
                ->where('item_id', $id)
                ->whereRaw('rg_goods_delivered.id = rg_goods_delivered_items.goods_delivered_id');
        });

        //POS oreders
        $posOrders = DB::connection('tenant')
        ->table('rg_pos_orders')
        ->select('id', 'tenant_id', 'date', 'number', 'status', 
            DB::raw("'pos_order' as content"), 
            DB::raw("'POS' as txn_code"),
            DB::raw("'pos/orders' as link")
        )
        ->where('tenant_id', session('tenant_id'))
        ->where(function($q) {
            $q->whereNotIn('status', ['edited']);
            $q->orWhereNull('status');
        })
        ->whereExists(function($query) use ($id) {
            $query->select(DB::raw(1))
                ->from('rg_pos_order_items')
                ->where('item_id', $id)
                ->whereRaw('rg_pos_orders.id = rg_pos_order_items.pos_order_id');
        });

        //sales
        $sales = DB::connection('tenant')
        ->table('rg_sales')
        ->select('id', 'tenant_id', 'date', DB::raw("'' as number"), 'status', 
            DB::raw("'sales' as content"), 
            DB::raw("'Sale' as txn_code"),
            DB::raw("'sales' as link")
        )
        ->where('tenant_id', session('tenant_id'))
        ->where(function($q) {
            $q->whereNotIn('status', ['edited']);
            $q->orWhereNull('status');
        })
        ->whereExists(function($query) use ($id) {
            $query->select(DB::raw(1))
                ->from('rg_sales_items')
                ->where('item_id', $id)
                ->whereRaw('rg_sales.id = rg_sales_items.sales_id');
        });

        $txns = DB::connection('tenant')
        ->table('rg_goods_received')
        ->select('id', 'tenant_id', 'date', 'number', 'status', 
            DB::raw("'goods_received' as content"), 
            DB::raw("'Received' as txn_code"),
            DB::raw("'goods-received' as link")
        )
        ->where('tenant_id', session('tenant_id'))
        ->where(function($q) {
            $q->whereNotIn('status', ['edited']);
            $q->orWhereNull('status');
        })
        ->whereExists(function($query) use ($id) {
            $query->select(DB::raw(1))
                ->from('rg_goods_received_items')
                ->where('item_id', $id)
                ->whereRaw('rg_goods_received.id = rg_goods_received_items.goods_received_id');
        })
        ->union($goodsDelivered)
        ->union($posOrders)
        ->union($sales)
        ->orderBy('date', 'desc')
        ->orderBy('id', 'desc')
        // ->toSql();
        ->paginate($per_page);

        foreach ($txns as $key => &$txn) 
        {
            if ($txn->content == 'goods_delivered')
            {
                $txn->items = GoodsDeliveredItem::where('goods_delivered_id', $txn->id)
                    ->where('item_id', $id)
                    ->get();
            }
            elseif ($txn->content == 'goods_received')
            {
                $txn->items = GoodsReceivedItem::where('goods_received_id', $txn->id)
                    ->where('item_id', $id)
                    ->get();
            }
            elseif ($txn->content == 'pos_order')
            {
                $txn->items = POSOrderItem::where('pos_order_id', $txn->id)
                    ->where('item_id', $id)
                    ->get();
            }
            elseif ($txn->content == 'sales')
            {
                $txn->items = SalesItem::where('sales_id', $txn->id)
                    ->where('item_id', $id)
                    ->get();
            }
            else
            {
                $txn->items = [];
            }
        }

        $item = Item::find($id);
        $item->append('inventory');

        return [
            'tableData' => $txns,
            'item' => $item
        ];
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

        //delete any delivery not with itemable_key pos_order_id
        GoodsDelivered::where('itemable_key', 'pos_order_id')->forceDelete();

        //Update items received
        $goodsReceived = GoodsReceived::with('items')->get();
        //return $goodsReceived->first()->toArray();
        foreach($goodsReceived as $t)
        {
            GoodsReceivedInventoryService::update($t->toArray());
        }

        //update items in POS orders
        $POSOrder = POSOrder::with('items')->get();
        foreach($POSOrder as $t)
        {   
            GoodsDeliveredInventoryService::update($t->toArray());
        }

        //update items in Sales
        $sales = Sales::with('items')->get();
        foreach($sales as $t)
        {   
            GoodsDeliveredInventoryService::update($t->toArray());
        }

        //Update items delivered
        $GoodsDelivered = GoodsDelivered::with('items')->get();
        foreach($GoodsDelivered as $t)
        {   
            GoodsDeliveredInventoryService::update($t->toArray());
        }
        

        //update items issued
        $GoodsIssued = GoodsIssued::with('items')->get();
        foreach($GoodsIssued as $t)
        {
            GoodsIssuedInventoryService::update($t->toArray());
        }

        //update items returned
        $GoodsReturned = GoodsReturned::with('items')->get();
        foreach($GoodsReturned as $t)
        {
            GoodsReturnedInventoryService::update($t->toArray());
        }

        return [
            'status' => true,
            'messages' => ['Inventory recomputing complete'],
        ];
    }

    

}
