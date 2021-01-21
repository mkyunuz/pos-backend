<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Warehouses;
use App\Users;
class UserHasWarehouse extends Model
{
    protected $table="user_has_warehouse";
    protected $fillable = ['user_id', "warehouse_id", "modified_by"];
    
    public function user(){
    	return $this->belongsTo(Users::class, "user_id", "id");
    }

    public function warehouse(){
    	return $this->belongsTo(Warehouses::class, "warehouse_id", "id");
    }

    public function sync($user_id, $warehouses){
        $warehouse_id = [];
        $data = [];
        foreach ($warehouses as $key => $val) {
            $warehouse = Warehouses::where(DB::raw("sha1(id)"), $val)->get()->first();
            if($warehouse){
                $this->where("user_id" , $user_id)->where("warehouse_id", "!=", $warehouse->id)->delete();
                $current_query = $this->where(["user_id" => $user_id, "warehouse_id" => $warehouse->id]);
                $current = $current_query->get()->first();

                $warehouse_id[] = $warehouse->id;
                if(!$current){
                    $data[] = [
                        "warehouse_id" => $warehouse->id,
                        "user_id" => $user_id,
                    ];
                }
            }
        }
        return $this->insert($data);
    }
}
