<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelpers;
use App\Models\TransactionDetail;
use App\Models\ReturnDetail;
use App\Models\Returns;
use App\Models\ProductHasWarehouse;
use App\Models\Transaction;
use App\Prices;
use App\Models\Purchase;
use App\Products;
use App\Repositories\DashboardRepo;
class DashboardController extends Controller
{
    public $response;
    public function __construct(){
    	$this->response = new AppHelpers();
    }
    public function summarySales(Request $request){
        $static_month_last = "2020-08";
        $static_month= "2020-11";
        $payload = null;
        try{

            

            

            $hpp = DashboardRepo::getHpp();
            $return = DashboardRepo::getReturn();
            $omzet = DashboardRepo::getOmzet();
            // return $omzet;
            $payload["omzet"] = $omzet["amount"];
            $payload["hpp"] = $hpp;
            $payload["gross_profit"] = $payload["omzet"] - $hpp;
            $payload["returns"] = $return["amount"];
        }catch(\Exception $e){
            return AppHelpers::error($e, 500);
        }
        return $this->response->json($payload, 200); 
    }
    public function omzet(Request $request){
        $payload = null;
        try{
            $payload = DashboardRepo::getOmzet();
        }catch(\Exception $e){
            return AppHelpers::error($e, 500);
        }
        return $this->response->json($payload, 200);
    }
    


    public function hpp(Request $request){
        $payload = null;
        try{
            $payload = DashboardRepo::getHpp();
        }catch(\Exception $e){
            return AppHelpers::error($e, 500);
        }
        return $this->response->json($payload, 200);
    }

    public function monthlySales(){
        $payload = null;
        try{
            $payload = DashboardRepo::getMonthlySales();
        }catch(\Exception $e){
            return AppHelpers::error($e, 500);
        }
        return $this->response->json($payload, 200);
    }


}
