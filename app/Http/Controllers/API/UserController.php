<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nexmo\Laravel\Facade\Nexmo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\Rule;
use Illuminate\Validation\Rule as ValidationRule;
use App\Notifications\WelcomeSmsNotification;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function smsnotificationuser(Request $request)
    {
        $phone = '+216' . $request->input('phone');

        $name = $request->input('name');

        Nexmo::message()->send([
            'to' => $phone,
            'from' => 'E-Saff APP',
            'text' => "Dear $name,
            This is an AI E-Saff reminder, your turn for [service] at [location] is coming up soon. Please make sure to arrive on time and have any necessary documents or information with you. Thank you!"
        ]);
    }




    public function index()
    {
        $users = User::all();
        return response()->json([
            'status' => 200,
            'users' => $users,
        ]);
    }
    public function allusers()
    {
        //$users = User::where('status','0')->get();
        $users = User::where('role', '!=', 'superadmin')->get();
        //$users = User::all();
        //$users = User::where('role','admin')->get();
        return response()->json([
            'status' => 200,
            'users' => $users,
        ]);
    }

    public function alladminusers()
    {
        $users = User::where('role', 'admin')->get();
        return response()->json([
            'status' => 200,
            'users' => $users,
        ]);
    }

    public function StaticUsers()
    {
        $users = User::all();
        $totalUsers = $users->count();

        $activeUsers = $users->where('status', 1)->count();
        $inactiveUsers = $users->where('status', 0)->count();

        return response()->json([
            'status' => 200,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
        ]);
    }


    public function edit($id)
    {
        $user = User::find($id);
        if ($user) {
            return response()->json([
                'status' => 200,
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'user not found',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191',
            'email' => [
                'required',
                'email',
                'max:191',
                ValidationRule::unique('users')->ignore($user->id),
            ],
            //'password' => 'required|max:191',
            'cin' => [
                'required',
                'numeric',
                'digits:8',
                ValidationRule::unique('users')->ignore($user->id),
            ],
            'phone' => [
                'required',
                'numeric',
                'digits:8',
                ValidationRule::unique('users')->ignore($user->id),
            ],
            //'cin' => 'required|numeric|digits:8|unique:users,cin',
            //'phone' => 'required|numeric|digits:8|unique:users,phone',
            'role' => 'required|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        //$user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        $user->cin = $request->input('cin');
        $user->phone = $request->input('phone');
        $user->status = $request->input('status');
        $user->role = $request->input('role');

        if ($request->hasFile('image')) {
            $path = $user->image;
            if (File::exists($path)) {
                File::delete($path);
            }
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('uploads/user/', $filename);
            $user->image = 'uploads/user/' . $filename;
        }
        $user->update();

        return response()->json([
            'status' => 200,
            'message' => 'User updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'user not found',
            ]);
        }
        $user->delete();
        return response()->json([
            'status' => 200,
            'message' => 'user deleted successfully',
        ]);
    }

    public function sendemail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            //$user = new User;
            Mail::to($request->input('email'))->send(
                new SendEmail('hallloooooooo')
            );

            return response()->json([
                'message' => 'Email sent successfully',
                'status' => 200,
            ]);
        }
    }

}
