<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Products;

trait ProductsTrait{

	public function product() : BelongsTo {
		return $this->belongsTo(Products::class);
	}

}