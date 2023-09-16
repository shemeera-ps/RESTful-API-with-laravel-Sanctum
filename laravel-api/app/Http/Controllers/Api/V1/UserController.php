<?php

namespace App\Http\Controllers\Api\V1;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\HttpResponse;

class UserController extends Controller
{
    use HttpResponse;
    public function createUser(Request $request){
        try{
            $validateUser=Validator::make($request->all(),
            [
                "name"=>'required',
                'email'=>'required|email|unique:users,email',
                'password'=>'required',
            ]);
            if($validateUser->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>'validation error',
                    'errors'=>$validateUser->errors()
                ],401);
            }
            $user=User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password)
            ]);
            return response()->json([
                'status'=>true,
                'message'=>'User created Successfully',
                'token'=>$user->createToken("API TOKEN")->plainTextToken
            ],200);
        }
        catch(\Throwable $th){
            return response()->json([
                'status'=>false,
                'message'=>$th->getMessage()
            ],500);
        }
    }
/**
 * *Login the user
 * *@param Request $request
 * *@return User
 **/
public function loginUser(Request $request){
    try{
        $validateUser=Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if($validateUser->fails()){
            return response()->json([
                'status'=>false,
                'message'=>'validation error',
                'errors'=>$validateUser->errors()
            ],401);
        }
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'status' => false,
                'message' => 'Email and password do not match our records',
            ], 401);
        }        
        $user=User::where('email',$request->email)->first();
        return response()->json([
            'status'=>true,
            'message'=>'User logged in successfully',
            'token'=>$user->createToken("API TOKEN")->plainTextToken
        ],200);
    }
    catch(\Throwable $th){
        return response()->json([
            'status'=>false,
            'message'=>$th->getMessage()
        ],500);
    }
}
public function logOutUser(Request $request){
    Auth::user()->currentAccessToken()->delete();
    return $this->success([
        'message'=>'You have successfully been logged out and your token has been deleted'
    ]);
}
}