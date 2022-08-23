<?php

namespace Rutatiina\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Rutatiina\Tenant\Scopes\TenantIdScope;

class Inventory extends Model
{
    use LogsActivity;

    protected static $logName = 'Inventory';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_inventory';

    protected $primaryKey = 'id';

    protected $guarded = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TenantIdScope);
    }

    public function item()
    {
        return $this->hasOne('Rutatiina\Item\Models\Item', 'id', 'item_id');
    }

}
