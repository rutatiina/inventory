<?php

namespace Rutatiina\Inventory\Http\Controllers;

use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Rutatiina\Contact\Traits\ContactTrait;
use Rutatiina\GoodsReturned\Models\GoodsReturned;
use Rutatiina\GoodsReturned\Traits\Item as TxnItem;

use Illuminate\Support\Facades\Request as FacadesRequest;
use Rutatiina\GoodsReturned\Services\GoodsReturnedService;
use Rutatiina\FinancialAccounting\Traits\FinancialAccountingTrait;
use Rutatiina\Inventory\Models\Inventory;
use Rutatiina\Item\Models\Item;
use Illuminate\Support\Str;


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

        $per_page = ($request->per_page) ? $request->per_page : 20;

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
            $query->has('inventory_records');
        }

        $query->latest();
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

    

}
