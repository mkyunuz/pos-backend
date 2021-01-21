<?php

namespace App\Repositories;
use App\Models\PendingTransactions;
use Illuminate\Support\Facades\DB;
class PendingTransactionsRepo{

    public static function filter($search = null, $query = [], $sort = [], $limit = 10, $offset = 0, $no_pagination = false){
        $model = PendingTransactions::where("id", "!=", "");
        if(is_array($query)){

        }

        if(is_array($query) && count($query) > 0){
            $allow_filter = ["transaction_number"];
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
            return $query->where("transaction_number", "like", "%".$search."%")
                    ->orWhere("product_name", "like", "%".$search."%");
        })->get()->map(function($data, $index ) use(&$no){
            $index = 0;
            return [
                "no" => $no++, 
                "key" => sha1($data->id),  
                "warehouse_id" => $data->warehouse_id,  
                "transaction_date" => $data->transaction_date, 
                "created" => $data->created_at->format('d M Y H:i:s'), 
                "last_modified" => $data->updated_at->format('d M Y H:i:s'),
                "detail" => $data->detail->map(function($data) use(&$index){
                    return [
                        "index" => $index++,
                        "unit_id" => $data->unit_id,
                        "unit" => $data->unit->unit_name,
                        "qty" => $data->qty,
                        "discount" => $data->discount,
                        "barcode" => $data->hasUnit->barcode,
                        "conversion" => $data->hasUnit->qty,
                        "price" => $data->price,
                        "prices" => $data->hasUnit->getPrice->map(function($data) {
                            return [
                                "price_group" => $data->price_group,
                                "qty" => $data->qty,
                                "price" => $data->price,
                            ];
                        }), 
                        "total" => $data->subtotal,
                        "ppn" => $data->ppn,
                        "product_id" => $data->product_id,
                        "product_name" => $data->product->product_name,
                    ];
                })
            ];
        });
        return ["totalRecords" => $totalRecords, "data" => $data];
    }

    public static function findByIdEncrypted($id){
        try {
            $model = PendingTransactions::where(DB::raw("SHA1(id)"), $id)->get()->first();
            $data = null;
            $total_amount = $model->payments->sum("amount") ?? 0;
            $total_paid = $model->payments->sum("paid_amount") ?? 0;
            $grand_total = $model->gt_after_ppn ?? 0;
            $change = $total_paid - $grand_total;
            if($change <= 0){
                $change = 0;
            }
            if($model){
                $data = [
                    "key" => sha1($model->id),
                    "transaction_number" => $model->transaction_number,
                    "warehouse_id" => sha1($model->warehouse_id),
                    "warehouse_name" => $model->warehouse,
                    "supplier_id" => sha1($model->supplier_id),
                    "total_ppn" => $model->total_ppn,
                    "grand_total" => $grand_total,
                    "payment_method" => $model->payment_method,
                    "due_date" => $model->due_date,
                    "shipping_costs" => $model->shipping_costs,
                    "transaction_date" => $model->transaction_date,
                    "remark" => $model->remark,
                    "service" => $model->service ?? 0,
                    "total_amount" => $total_amount,
                    "total_paid" => $total_paid,
                    "change" => $change,
                    "full_payment" => $model->full_payment,
                    "phone_number" => $model->phone_number,
                    "items" => $model->detail->map(function($data){
                        return [
                            "product_name" => $data->product->product_name,
                            "price" => $data->price,
                            "ppn" => $data->ppn,
                            "qty" => $data->qty,
                            "subtotal" => $data->subtotal,
                            "discount" => $data->discount,
                            "unit" => $data->productConversions->unit->unit_name,
                            "unit_id" => $data->unit_id,
                        ];
                    }),
                    "gt_after_ppn" => $model->gt_after_ppn,
                    "total_discount" => $model->total_discount,
                    "status" => ($model->close == 1) ? "Lunas" : "Dibayar",
                    "payments" => $model->payments->map(function($data) {
                        return [
                            "amount" => $data->amount,
                            "paid_amount" => $data->paid_amount,
                            "due" => $data->due,
                            "created" => $data->created_at->format("Y-m-d"),
                            "id" => sha1($data->id),
                        ];
                    }),
                ];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $data;
    }


}