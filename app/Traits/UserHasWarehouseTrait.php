<?php
namespace App\Traits;
use Illuminate\Database\Eloquent\Relations\HasMany;
trait UserHasWarehouseTrait{
	public function warehouses() : HasMany {
		return $this->hasMany("App\Models\UserHasWarehouse", "user_id", "id");
	}
}