<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Transaction;
use App\Models\ProductHasUnit;
use App\Products;

trait PosTrait{

	public function transaction() : BelongsTo {
		return $this->belongsTo(Transaction::class, "transaction_id", "id");
	}

	public function warehouse($warehouse_id) : BelongsTo{
		return $this->belongsTo(Products::class, "product_id", "product_id")->where("warehouse_id", $warehouse_id);
	}

	public function product() : BelongsTo {
		return $this->belongsTo(Products::class, "product_id", "id");
	}
	public function productConversions() : BelongsTo {
		$data = $this->belongsTo(ProductHasUnit::class, "unit_id", "unit_id");
		return $data;
	}
}