<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function(){
	Route::prefix("categories")->group(function(){
		Route::post("/", "CategoriesController@index");
		Route::post("/save", "CategoriesController@save");
		Route::post("/update", "CategoriesController@update");
		Route::post("/delete", "CategoriesController@delete");
		Route::get("/view", "CategoriesController@view");
		Route::get("/check-id", "CategoriesController@checkCategoryId");
	});

	Route::prefix("suppliers")->group(function(){
		Route::post("/", "SuppliersController@index");
		Route::post("/save", "SuppliersController@save");
		Route::post("/update", "SuppliersController@save");
		Route::post("/delete", "SuppliersController@delete");
		Route::get("/view", "SuppliersController@view");
	});

	Route::prefix("units")->group(function(){
		Route::post("/", "UnitsController@index");
		Route::post("/save", "UnitsController@save");
		Route::post("/update", "UnitsController@save");
		Route::post("/delete", "UnitsController@delete");
		Route::get("/view", "UnitsController@view");
		Route::get("/check-id", "UnitsController@checkUnitId");
	});
	Route::prefix("warehouses")->group(function(){
		Route::post("/", "WarehousesController@index");
		Route::post("/save", "WarehousesController@save");
		Route::post("/update", "WarehousesController@save");
		Route::post("/delete", "WarehousesController@delete");
		Route::get("/view", "WarehousesController@view");
	});
	Route::prefix("products")->group(function(){
		Route::post("/", "ProductsController@index");
		Route::post("/save", "ProductsController@save");
		Route::post("/update", "ProductsController@save");
		Route::post("/delete", "ProductsController@delete");
		Route::get("/view", "ProductsController@view");
		Route::get("/check-id", "ProductsController@checkProductId");
		Route::get("/check-barcode", "ProductsController@checkBarcode");
	});
	Route::prefix("po")->group(function(){
		Route::get("/po-number", "PoController@getPoNumber");
		Route::post("/", "PoController@index");
		Route::post("/save", "PoController@save");
		Route::post("/update", "PoController@save");
		// Route::post("/delete", "ProductsController@delete");
		Route::get("/view", "PoController@view");
		// Route::get("/check-id", "ProductsController@checkProductId");
		// Route::get("/check-barcode", "ProductsController@checkBarcode");
	});
	Route::prefix("purchase")->group(function(){
		Route::get("/purchase-number", "PurchaseController@getPoNumber");
		Route::post("/", "PurchaseController@index");
		Route::post("/save", "PurchaseController@save");
		Route::post("/update", "PurchaseController@save");
		// Route::post("/delete", "ProductsController@delete");
		Route::get("/view", "PurchaseController@view");
		// Route::get("/check-id", "ProductsController@checkProductId");
		// Route::get("/check-barcode", "ProductsController@checkBarcode");
	});
});
