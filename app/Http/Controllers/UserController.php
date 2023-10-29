<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id)
    {
        $user = User::find($id);
        $start_date = (strtotime("last sunday") + (24 * 3600)) * 1000;
        $end_week_date = ((strtotime("last sunday") + (7 * 86400)) + 24 * 3600) * 1000;
        $start_today_date = strtotime("today") * 1000;
        $end_today_date = (strtotime("today") + (24 * 3600)) * 1000;


        $today = Transaction::where('user_id', $id)->where('timestamp', '>=', $start_today_date)->where('timestamp', '<=', $end_today_date)->count();
        $week = Transaction::where('user_id', $id)->where('timestamp', '>=', $start_date)->where('timestamp', '<=', $end_week_date)->count();

        if ($user) {
            $user->img = url('img/profile', $user->img);
            return response()->json([
                'code' => 200,
                'message' => 'User Found!',
                'data' =>  $user,
                'trx_today' => $today,
                'trx_week' => $week
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'User Not Found!',
                'data' => null,
                'trx_today' => null,
                'trx_week' => null
            ], 404);
        }
    }

    public function uploadImg(Request $request)
    {
        $id = $request->input('id');
        $img = $request->file('img');

        $user = User::find($id);
        if ($user) {
            if ($user->img != 'default.png') {
                $link = explode('/', $user->img);
                $path = base_path() . '\public\img\profile\\' . end($link);
                if (File::exists($path)) {
                    unlink($path);
                }
            }


            if ($img) {
                $img_name = 'profile_' . time() . '.' . $img->getClientOriginalExtension();
                $img->move('img/profile', $img_name);
                $user->update([
                    'img' => $img_name,
                ]);
            }
            $user->img = url('img/profile', $user->img);
            return response()->json([
                'code' => 200,
                'message' => 'User Found!',
                'data' =>  $user
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'User Not Found!',
                'data' => null
            ], 404);
        }
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'owner' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required',
            'name' => 'required',
            'description' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json([
                'code' => 422 ,
                'message' => $error,
                'data' => null
            ], 422);
        } else {
            $id = $request->input('id');
            $owner = $request->input('owner');
            $email = $request->input('email');
            $phoneNumber = $request->input('phone_number');
            $name = $request->input('name');
            $desc = $request->input('description');
            $address = $request->input('address');

            $user = User::find($id);
            if ($user) {
                $user->update([
                    'owner' => $owner,
                    'phone_number' => $phoneNumber,
                    'email' => $email,
                    'name' => $name,
                    'description' => $desc,
                    'address' => $address
                ]);

                $user->img = url('img/profile', $user->img);
                return response()->json([
                    'code' => 200,
                    'message' => 'User has been update!',
                    'data' => $user
                ], 200);
            } else {
                return response()->json([
                    'code' => 404,
                    'message' => 'User Not Found!',
                    'data' => null
                ], 404);
            }
        }
    }
}
