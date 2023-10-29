<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
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
            return response()->json(Product::where('user_id', $user_id)->paginate(10), 200);
        }
    }

    public function createUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'code' => 'required',
            'name' => 'required',
            'price' => 'required',
            'stock' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json([
                'code' => 422,
                'message' => $error,
                'data' => null
            ], 422);
        } else {
            if ($request->input('id')) {
                return $this->update($request);
            } else {
                return $this->create($request);
            }
        }
    }

    public function create(Request $request)
    {
        $user_id = $request->input('user_id');
        $img = $request->file('img');
        $code = $request->input('code');
        $name = $request->input('name');
        $price = $request->input('price');
        $stock = $request->input('stock');

        $data = [
            'user_id' => $user_id,
            'code' => $code,
            'name' => $name,
            'img' => 'default.png',
            'price' => $price,
            'stock' => $stock
        ];

        if ($img) {
            $img_name = 'product_' . time() . '.' . $img->getClientOriginalExtension();
            $img->move('img/product', $img_name);
            $data['img'] = $img_name;
        }

        $product = Product::create($data);
        if ($product) {
            return response()->json([
                'code' => 200,
                'message' => 'Product has been Created!',
                'data' => $product
            ], 200);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Create Product Failed!',
                'data' => null
            ], 400);
        }
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $user_id = $request->input('user_id');
        $img = $request->file('img');
        $code = $request->input('code');
        $name = $request->input('name');
        $price = $request->input('price');
        $stock = $request->input('stock');

        $product = Product::find($id);

        if ($product) {
            if ($product->img != 'default.png') {
                $link = explode('/', $product->img);
                $path = base_path() . '\public\img\product\\' . end($link);
                if (File::exists($path)) {
                    unlink($path);
                }
            }

            $data = [
                'user_id' => $user_id,
                'code' => $code,
                'name' => $name,
                'price' => $price,
                'stock' => $stock
            ];

            if ($img) {
                $img_name = 'product' . time() . '.' . $img->getClientOriginalExtension();
                $img->move('img/product', $img_name);
                $data['img'] = $img_name;
            }


            $product->update($data);
            return response()->json([
                'code' => 200,
                'message' => 'Product has been update!',
                'data' => $product
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'Product Not Found!',
                'data' => null
            ], 404);
        }
    }

    public function delete($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return response()->json([
                'code' => 200,
                'message' => 'Product has been Delete!',
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'Product Not Found!',
            ], 404);
        }
    }

    public function findById($id)
    {
        $product = Product::find($id);
        if ($product) {
            return response()->json([
                'code' => 200,
                'message' => 'Product Found!',
                'data' =>  $product
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'Product Not Found!',
                'data' => null
            ], 404);
        }
    }

    public function findByUserIdCode(Request $request)
    {
        $user_id = $request->query('user_id');
        $code = $request->query('code');
        $product = Product::where('user_id', $user_id)->where('code', $code)->first();
        if ($product) {
            return response()->json([
                'code' => 200,
                'message' => 'Product Found!',
                'data' =>  $product
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'Product Not Found!',
                'data' => null
            ], 404);
        }
    }
}
