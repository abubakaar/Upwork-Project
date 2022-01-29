<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Nette\Utils\Random;
use Validator;

class AuthenticationController extends Controller
{

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['resp_code' => 401, 'error' => $validator->errors()->all()]);
        } else {
            $otp = mt_rand(100000, 999999);
            $user = User::insert([
                'email' => $request->get('email'),
                'password' => bcrypt($request->password),
                'otp' => $otp
            ]);

            $details = [
//                'title' => 'OTP',
                'otp' =>  $otp
            ];
            $email = $request->get('email');
            Mail::to($email)->send(new \App\Mail\MyOTPMail($details));

            return response()->json(['resp_code' => 200, 'message' => "OTP sent successfully"]);
        }

    }


    public function profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|min:4|max:20',
            'user_name' => 'required',
            'avatar' => 'dimensions:min_width=256,min_height=256|required'
        ]);
        if ($validator->fails()) {
            return response()->json(['resp_code' => 401, 'error' => $validator->errors()->all()]);
        } else {
            $user = User::where('id', $request->get('id'))->first();
            if($user) {
                $user->name = $request->get('name');
                $user->user_name = $request->get('user_name');
                $user->avatar = $request->file('avatar')->getRealPath();
                $user->save();
                return response()->json(['resp_code' => 200, 'message' => 'Profile Updated Successfully']);
            }else{
                return response()->json(['resp_code' => 401, 'error' => 'No user found']);
            }
        }
    }


    public function confirmPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['resp_code' => 401, 'error' => $validator->errors()->all()]);
        } else {
            $user = User::where('email', $request->get('email'))->first();
            if ($user && $request->get('otp') == $user->otp) {
                $user->registered_at = Carbon::now();
                $user->save();
                $token = $user->createToken('myapptoken')->plainTextToken;
                return response()->json(['resp_code' => 200, 'message' => "Account created successfully", 'data' => ['user' => $user, 'token' => $token]]);
            } else {
                return response()->json(['resp_code' => 200, 'message' => "OTP validation failed"]);
            }


        }

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->get('email'))->first();
        if((!$user || !Hash::check($request->get('password'), $user->password) ) && !$user->registered_at){
            return response()->json(['resp_code' => 400, 'error' => 'Bad Credentials']);
        }else{
            $token = $user->createToken('myapptoken')->plainTextToken;
            return response()->json(['resp_code' => 200, 'message' => "login successfully", 'data' => ['user' => $user, 'token' => $token]]);
        }
    }

    public function adminSendMail(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        $details = [
            'title' => 'Invitaion Link for Signup',
            'body' => 'Please use this link to register yourself  localhost:8000/api/register '
        ];
        $email = ($request->get('email'));
        Mail::to("$email")->send(new \App\Mail\MyTestMail($details));
        return response()->json(['resp_code' => 200, 'message' => "Inviataion Send successfully"]);
    }


}
