<?php
namespace App\Repositories;

use App\Models\TransactionDetail;
use App\Models\Transaction;
use App\Models\ReturnDetail;
use App\Helpers\AppHelpers;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
class DashboardRepo {
	// public static $static_mont= "2020-08";
	public static $static_month= "2020-09";
	// public static $static_mont= date("Y-m");
	public static function getOmzet($date = null){
		$data = null;
		try{
			/*$model = TransactionDetail::whereHas("transaction", function($q){
		       return  $q->where(DB::raw("date_format(transaction_date, '%Y-%m')"), self::$static_mont);
		    });
		    $data = $model->sum(DB::raw("price * qty"));
		    $data = round($data, 2);*/
		    $model = TransactionDetail::whereHas("transaction", function($q){
		       return  $q->where(DB::raw("date_format(transaction_date, '%Y-%m')"), self::$static_month);
		    });
		    $omzet = $model->sum(DB::raw("price * qty"));
		    $hpp = $model->sum(DB::raw("hpp * qty"));
		    return ["amount"=> $omzet, "hpp" => $hpp];
		}catch(\Exception $e){
		    return AppHelpers::error($e, 500);
		}
	    return $data;
	}
	public static function getReturn($date = null){
		$data = null;
		try{
			$model = ReturnDetail::whereHas("_return", function($q){
		       return  $q->where(DB::raw("date_format(return_date, '%Y-%m')"), self::$static_month);
		    });
		    $return = $model->sum(DB::raw("amount"));
		    $hpp = $model->sum(DB::raw("hpp * qty"));
		    $data = ["amount" => round($return, 2), "hpp" => round($hpp, 2) ];
		}catch(\Exception $e){
		    return AppHelpers::error($e, 500);
		}
	    return $data;
	}
	public static function getHpp($date = null){
		$data = null;
		try{
			/*$model = TransactionDetail::whereHas("transaction", function($q){
		       return  $q->where(DB::raw("date_format(transaction_date, '%Y-%m')"), self::$static_month);
		    });

		    // $data = $model->sum(DB::raw("hpp * qty"));
		    $data = $model->get()->sum(function($data){
		    	$return = $data->returns->sum("qty");
		    	return ($data->qty - $return) * $data->hpp;
		    });
		    $data = round($data, 2);*/
		    $purchase = Purchase::where(DB::raw("date_format(transaction_date, '%Y-%m')"), static::$static_month)->sum("gt_after_ppn");
		    $model_penjualan = TransactionDetail::whereHas("transaction", function($q) {
		       return  $q->where(DB::raw("date_format(transaction_date, '%Y-%m')"), static::$static_month);
		    });
		    $penjualan = $model_penjualan->get()->sum(function($data){
		        $return = $data->returns->sum("qty");
		        return ($data->qty) * $data->hpp;
		    });

		    $model_return = ReturnDetail::whereHas("_return", function($q) {
		       return  $q->where(DB::raw("date_format(return_date, '%Y-%m')"), static::$static_month);
		    });
		    $return_amount = $model_return->sum(DB::raw("amount"));
		    $return_hpp = $model_return->sum(DB::raw("hpp * qty"));

		    $penjualan = round($penjualan, 2);
		    $persediaan = $purchase;
		    $barang_tersedia_untuk_dijual = $purchase;
		    $persediaan_akhir = $barang_tersedia_untuk_dijual - $penjualan;
		    $hpp = $barang_tersedia_untuk_dijual - $persediaan_akhir;
		    return round($hpp - $return_hpp, 2);
		}catch(\Exception $e){
		    return AppHelpers::error($e, 500);
		}
	    return $data;
	}

	public static function getMonthlySales(){
			$data = null;
			try{
				$model = TransactionDetail::select(
							DB::raw("transactions.transaction_date as name, transactions.transaction_date as date, sum(transaction_detail.subtotal) as value")
						)->join("transactions", "transaction_detail.transaction_id", "transactions.id")
							->where(DB::raw("date_format(transactions.transaction_date, '%Y-%m')"), static::$static_month)->groupBy("transactions.transaction_date");
				$data = $model->get();
			}catch(\Exception $e){
			    return AppHelpers::error($e, 500);
			}
		    return $data;
	}
}