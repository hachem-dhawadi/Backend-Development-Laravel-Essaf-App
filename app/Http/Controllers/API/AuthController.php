<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function getCurrentUser()
    {
        $user = auth()->user();
        return response()->json([
            'user' => $user
        ]);
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            $user = new User,
            'name' => 'required|max:191',
            'email' => ['required', 'email', 'max:191', 'unique:users,email', function ($attribute, $value, $fail) {
                $validDomains = ['gmail.com', 'yahoo.com'];
                $domain = substr(strrchr($value, "@"), 1);
                if (!in_array($domain, $validDomains)) {
                    $fail($attribute.' domain is not allowed.');
                }
            }],
            'password' => 'required|min:8',
            'cin' => 'required|numeric|digits:8|unique:users,cin',
            'phone' => 'required|numeric|digits:8|unique:users,phone',
            'role' => 'required|max:191',
            'image' => 'required|image|mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG',
            //'image' => 'mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG',
            //'service' => 'required|mimes:pdf',
            //'service' => ($user->role === 'admin') ? 'required|mimes:pdf' : 'nullable|mimes:pdf',
            //'service' =>'nullable|mimes:pdf',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'validation_errors' => $validator->getMessageBag(),
            ]);
        } else {
            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
            $user->cin = $request->input('cin');
            $user->phone = $request->input('phone');
            $user->role = $request->input('role');
            $user->service = $request->input('service');
            if ($user->role == 'client') {
                $user->status = 1;
            } else if ($user->role == 'counter-clerk') {
                $user->status = 1;
            } else {
                $user->status = 0;
            }

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/user/', $filename);
                $user->image = 'uploads/user/' . $filename;
            }
            if ($request->hasFile('service')) {
             $file = $request->file('service');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/file/', $filename);
                $user->service = 'uploads/file/' . $filename;
            /*$file = $request->file('service');
                $filename = $file->getClientOriginalName();
                $path = $file->store('uploads/file');
                $user->service = $path;*/
             }

            $user->save();

            $token = $user->createToken($user->email . '_Token')->plainTextToken;
            return response()->json([
                'status' => 200,
                'username' => $user->name,
                'token' => $token,
                'message' => 'Thank you for registering with us ! Stay tuned on your email and phone for updates. We look forward to serving you.',
            ]);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|max:191',
            'password' => 'required',
            //'status' =>'max:1',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'validation_errors' => $validator->getMessageBag(),
            ]);
        } else {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid Credetials'
                ]);
            } elseif ($user->status == 0) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Your account is still inactive , keep checking your email'
                ]);
            } else {
                $token = $user->createToken($user->email . '_Token')->plainTextToken;
                $role = $user->role;
                return response()->json([
                    'status' => 200,
                    'username' => $user->name,
                    'token' => $token,
                    'role' => $role,
                    'message' => 'Logged In Successfully',
                ]);
            }
        }
    }



    public function logout()
    {
        //auth()->user()->tokens()->delete() 3lech mtkhdemch nheb nefhemha yjbkchi
        Auth::user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json([
            'status' => 200,
            'message' => 'Logged Out Successfully',
        ]);
    }

    //change password

    public function changepassword(Request $request){
        $validator = Validator::make($request->all(), [
            'old_password'=>'required',
            'password'=>'required|min:6|max:100',
            'confirm_password'=>'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors'=>$validator->errors()
            ]);
        }

        $user=$request->user();
        if(Hash::check($request->old_password,$user->password)){
            $user->update([
                'password'=>Hash::make($request->password)
            ]);
            return response()->json([
                'status' => 200,
                'message'=>'Password successfully updated',
            ]);
        }else{
            return response()->json([
                'status' => 400,
                'message'=>'Old password does not matched',
            ]);
        }

    }

    //forget password

    public function createpassword(Request $request, $token)
    {
        return response()->json([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function storepassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successful'])
            : response()->json(['message' => 'Unable to reset password'], 500);
    }

    
    


}
