<?php

use Illuminate\Support\Facades\Route;
use Rutatiina\Inventory\Http\Controllers\InventoryItemController;


Route::group(['middleware' => ['web', 'auth', 'tenant']], function() {

	Route::prefix('inventory')->group(function () {

        Route::get('items', [InventoryItemController::class, 'items']);
        Route::get('items/{id}', [InventoryItemController::class, 'item']);
        Route::post('recompute', [InventoryItemController::class, 'recompute']);

    });

    // Route::resource('goods-returned/settings', 'Rutatiina\GoodsReturned\Http\Controllers\GoodsReturnedSettingsController');

});
