<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get("/customers", "CustomerController@index");
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get("/api-module-generator", "ApiModuleGenerator@index")->name("ApiModuleGenerator");
Route::post("/api-module-generator", "ApiModuleGenerator@generate")->name("ApiModuleGeneratorGenerate");
Route::prefix("po")->group(function(){
		Route::get("/po-number", "PoController@getPoNumber");
		// Route::post("/", "ProductsController@index");
		Route::post("/save", "PoController@save");
		// Route::post("/update", "ProductsController@save");
		// Route::post("/delete", "ProductsController@delete");
		// Route::get("/view", "ProductsController@view");
		// Route::get("/check-id", "ProductsController@checkProductId");
		// Route::get("/check-barcode", "ProductsController@checkBarcode");
	});

Route::prefix("journals")->group(function(){
		Route::get("/", "PurchaseController@journal");
		Route::get("/bukubesar", "JournalController@bukubesar");
		// Route::post("/", "ProductsController@index");
		// Route::post("/save", "PoController@save");
		// Route::post("/update", "ProductsController@save");
		// Route::post("/delete", "ProductsController@delete");
		// Route::get("/view", "ProductsController@view");
		// Route::get("/check-id", "ProductsController@checkProductId");
		// Route::get("/check-barcode", "ProductsController@checkBarcode");
	});

Route::prefix("pos")->group(function(){
	Route::get("/products", "PosController@products");
	Route::get("/summary", "PosController@summary");
});
Route::prefix("products")->group(function(){
	Route::get("/hpp", "ProductsController@hpp");
});

Route::prefix("ar")->group(function(){
	Route::get("/", "AccountReceivableController@index");
});
