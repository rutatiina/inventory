<?php

use Illuminate\Support\Facades\Route;
use Rutatiina\Inventory\Http\Controllers\InventoryItemController;


Route::group(['middleware' => ['web', 'auth', 'tenant']], function() {

	Route::prefix('inventory')->group(function () {

        Route::get('items', [InventoryItemController::class, 'index']);

    });

    // Route::resource('goods-returned/settings', 'Rutatiina\GoodsReturned\Http\Controllers\GoodsReturnedSettingsController');

});
