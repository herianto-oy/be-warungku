<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request)
    {
        $user_id = $request->get('id', null);
        if (!$user_id) {
            return response()->json([
                'current_page' => 1,
                'total' => 0,
                'data' => []
            ], 404);
        } else {
            return response()->json(Transaction::with('details', 'details.product')->where('user_id', $user_id)->orderBy('timestamp', 'DESC')->paginate(10));
        }
    }

    public function create(Request $request)
    {
        $user_id = $request->input('user_id');
        $name = $request->input('name');
        $timestamp = $request->input('timestamp');
        $details = $request->input('details');
        $total =  $request->input('total');
        $status = $request->input('status');

        $data = [
            'user_id' => $user_id,
            'name' => $name,
            'timestamp' => $timestamp,
            'total' => $total,
            'status' => $status
        ];

        $trx = Transaction::create($data);
        foreach ($details as $value) {
            $d = [
                'transaction_id'  => $trx->id,
                'product_id' => $value['product']['id'],
                'qty' => $value['qty'],
                'price' =>  $value['price']
            ];

            $product = Product::find($value['product']['id']);
            $product->stock = $product->stock -  $value['qty'];
            $product->save();
            TransactionDetail::create($d);
        }

        if ($trx) {
            $data = Transaction::with('details', 'details.product')->find($trx->id);
            return response()->json([
                'code' => 200,
                'message' => 'Transaction has been Created!',
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Create Transaction Failed!',
                'data' => null
            ], 400);
        }
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $trx = Transaction::find($id);
        if ($trx) {
            $trx->status = 0;
            foreach ($trx->details as $value) {
                $product = Product::find($value->product_id);
                $product->stock = $product->stock +  $value->qty;
                $product->save();
            }
            $trx->save();
            return response()->json([
                'code' => 200,
                'message' => 'Transaction has been Canceled!',
                'data' => $trx
            ], 200);
        }else{
            return response()->json([
                'code' => 400,
                'message' => 'Transactio Cancel Failed!',
                'data' => null
            ], 400);
        }
    }


    public function findById($id)
    {
        $trx = Transaction::with('details', 'details.product')->find($id);
        if ($trx) {
            return response()->json([
                'code' => 200,
                'message' => 'Transaction Found!',
                'data' =>  $trx
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'Transaction Not Found!',
                'data' => null
            ], 404);
        }
    }
}
