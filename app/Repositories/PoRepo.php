<?php

namespace App\Repositories;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
class PoRepo{

    public static function generatePoNumber(){
        $prefix = date("Y.m");
        $data = PurchaseOrder::select(DB::raw("max(RIGHT(po_number, 5)) as current"))->where(DB::raw("LEFT(po_number, 7)") , $prefix)->get()->first();
        $current = 0;
        if($data->current) $current = (int) $data->current;
        $next = $current + 1;
        $nextString = str_pad($next, 5, '0', STR_PAD_LEFT);
        $po_number = $prefix . "-" . $nextString;
        return $po_number;
    }

    public static function filter($search = null, $query = [], $sort = [], $limit = 10, $offset = 0, $no_pagination = false){
        $model = PurchaseOrder::where("id", "!=", "");
        if(is_array($query)){

        }

        if(is_array($query) && count($query) > 0){
            $allow_filter = ["po_number"];
            foreach ($query as $key => $value) {
                if(in_array($key, $allow_filter)){
                    $model->where($key, "like", "%".$value."%");
                }
            }
        }
        if(is_array($sort) && count($sort) > 0){
            $allow_sort = array_merge($allow_filter, ["created_at", "updated_at"]);
            $dir = isset($sort["dir"]) ? $sort["dir"]  : "desc";
            $column = isset($sort["column"]) ? $sort["column"] : "created_at";
            if(in_array($column, $allow_sort) && in_array($dir, ["asc, desc"])){
                 $model->orderBy($column, $dir);
            }
        }

        $totalRecords = $model->count();
        if($no_pagination){
            $model->limit($limit);
            $model->offset($offset);
        }
        $no = $offset+1;
        $data = $model->when($search, function($query, $search){
            return $query->where("po_number", "like", "%".$search."%")
                    ->orWhere("product_name", "like", "%".$search."%");
        })->get()->map(function($data, $index ) use(&$no){
            return [
                "no" => $no++, 
                "key" => sha1($data->id), 
                "po_number" => $data->po_number, 
                "gt_after_ppn" => $data->gt_after_ppn, 
                "grand_total" => $data->grand_total, 
                "transaction_date" => $data->transaction_date, 
                "created" => $data->created_at->format('d M Y H:i:s'), 
                "last_modified" => $data->updated_at->format('d M Y H:i:s'), 
            ];
        });
        return ["totalRecords" => $totalRecords, "data" => $data];
    }

    public static function findByIdEncrypted($id){
        try {
            $model = PurchaseOrder::where(DB::raw("SHA1(id)"), $id)->get()->first();
            $data = null;
            if($model){
                $data = [
                    "id" => sha1($model->id),
                    "po_number" => $model->po_number,
                    "warehouse_id" => sha1($model->warehouse_id),
                    "supplier_id" => sha1($model->supplier_id),
                    "total_ppn" => $model->total_ppn,
                    "grand_total" => $model->grand_total,
                    "items" => $model->orders->map(function($data){
                        $unit = $data->product->_unit;
                        $units = [["unit_id" => sha1($unit->id), "unit_name" => $unit->unit_name]];
                        $conversions = $data->product->productConversions->map(function($data) use ($unit){
                            return [
                                "unit_id" => sha1($data->unit_id),
                                "unit_name" => $data->unit->unit_name,
                            ];
                        })->toArray();
                        try {
                            $units = array_merge($units, $conversions);
                        } catch (\Exception $e) {
                            
                        }
                        return [
                            "detail_id" => sha1($data->id),
                            "key" => sha1($data->product->id),
                            "product_code" => $data->product->product_code,
                            "product_name" => $data->product->product_name,
                            "qty" => $data->qty,
                            "unit_id" => sha1($data->unit_id),
                            "purchase_price" => $data->price,
                            "discount" => $data->discount,
                            "subtotal" => $data->subtotal,
                            "ppn" => $data->taxs,
                            "units" => $units
                        ];
                    }),
                    "payment_method" => $model->payment_method,
                    "due_date" => $model->due_date,
                    "shipping_costs" => $model->shipping_costs,
                    "transaction_date" => $model->transaction_date,
                    "remark" => $model->remark,
                    "company" => $model->company,
                    "phone_number" => $model->phone_number,
                    "shipping_to" => $model->shipping_to,
                ];
            }
        } catch (\Exception $e) {
            // return $e->getMessage();
        }

        return $data;
    }
	
}