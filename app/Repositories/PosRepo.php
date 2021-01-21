<?php

namespace App\Repositories;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
class PosRepo{

	public static function generateTransactionNumber(){
        $prefix = "SO".date("Y.m");
        $data = Transaction::select(DB::raw("max(RIGHT(transaction_number, 5)) as current"))->get()->first();
        $current = 0;
        if($data->current) $current = (int) $data->current;
        $next = $current + 1;
        $nextString = str_pad($next, 5, '0', STR_PAD_LEFT);
        $po_number = $prefix . "-" . $nextString;
        return $po_number;
    }

    public static function filter($search = null, $query = [], $sort = [], $limit = 10, $offset = 0, $no_pagination = false){
        $model = Transaction::where("id", "!=", "");
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
            $payment_amount = $data->payments()->groupBy("payment_id", "payment_type")->sum("amount");
            $due = ($data->gt_after_ppn + $data->service) - $payment_amount;
            return [
                "no" => $no++, 
                "key" => sha1($data->id), 
                "transaction_number" => $data->transaction_number, 
                "gt_after_ppn" => $data->gt_after_ppn, 
                "total_ppn" => $data->total_ppn, 
                "due" => $due, 
                "payment_amount" => $payment_amount, 
                "count_payment" => $data->payments->count(), 
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
            $model = Transaction::where(DB::raw("SHA1(id)"), $id)->get()->first();
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
                    "warehouse_name" => $model->warehouse->warehouse_name,
                    "supplier_id" => sha1($model->supplier_id),
                    "total_ppn" => $model->total_ppn,
                    "grand_total" => $grand_total,
                    "payment_method" => $model->payment_method,
                    "due_date" => $model->due_date,
                    "shipping_costs" => $model->shipping_costs,
                    "transaction_date" => $model->transaction_date,
                    "remark" => $model->remark,
                    "service" => $model->services ?? 0,
                    "total_amount" => $total_amount,
                    "total_paid" => $total_paid,
                    "change" => $change,
                    "full_payment" => $model->full_payment,
                    "phone_number" => $model->phone_number,
                    "items" => $model->detail->map(function($data){
                        return [
                            "product_id" => sha1($data->product_id),
                            "product_name" => $data->product->product_name,
                            "price" => $data->price,
                            "ppn" => $data->ppn,
                            "qty" => $data->qty,
                            "subtotal" => $data->subtotal,
                            "discount" => $data->discount,
                            "unit" => $data->productConversions->unit->unit_name,
                            "conversion" => $data->productConversions->qty,
                            "unit_id" => $data->unit_id,
                            "total_return" => $data->returns->sum("qty")
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

    public static function receiptOfPayment($key, $payment_id){
        $model =  Transaction::where(DB::raw("SHA1(id)"), $key)->get()->first();
        $model->payment_id = $payment_id;
        $total_amount = $model->payments->sum("amount") ?? 0;
        $total_paid = $model->payments->sum("paid_amount") ?? 0;
        $grand_total = $model->gt_after_ppn ?? 0;
        $change = $total_paid - $grand_total;
        if($change <= 0){
            $change = 0;
        }
        $data = null;
        if($model){
            $data = [
                "transaction_number" => $model->transaction_number,
                "payment" => $model->paymentOne,
                "total_ppn" => $model->total_ppn,
                "grand_total" => $grand_total,
                "payment_method" => $model->payment_method,
                "due_date" => $model->due_date,
                "shipping_costs" => $model->shipping_costs,
                "transaction_date" => $model->transaction_date,
                "transaction_date" => $model->transaction_date,
                "service" => $model->service,
                "total_amount" => $total_amount,
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
            ];
        }

        return $data;
    }

}