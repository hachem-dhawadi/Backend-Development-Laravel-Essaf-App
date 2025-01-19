<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use Carbon\Carbon;

class PayementController extends Controller
{
    public function AllOfPayements()
    {
        $payements = Payement::all();
        return response()->json([
            'status' => 200,
            'payements' => $payements,
        ]);
    }

    public function allPayements()
    {
        $user = auth()->user();
        $payements = Payement::where('user_id', $user->id)->get();

        return response()->json([
            'status' => 200,
            'payements' => $payements,
        ]);
    }

    public function StaticPayement()
    {
        $payments = Payement::all();
        $totalPayments = $payments->count();


        return response()->json([
            'status' => 200,
            'totalPayments' => $totalPayments,
        ]);
    }



    public function payer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adresse' => 'required',
            'date' => 'required',
            'code' => 'required|numeric|digits:12|unique:payement,code',
            'payement_id' => 'required|unique:payement,payement_id',
            'nb_services' => 'required|not_in:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $payement = new Payement;
            $payement->user_id = $request->input('user_id');
            $payement->adresse = $request->input('adresse');
            $payement->date = $request->input('date');
            $payement->code = $request->input('code');

            $payement->payement_id = $request->input('payement_id');
            $payement->payement_mode = $request->input('payement_mode');
            $payement->tracking_no = "E-Saff-" . rand(1111, 9999);
            $payement->money = $request->input('money');
            $payement->nb_services = $request->input('nb_services');
            $payement->active_plus = $request->input('active_plus');
            $payement->file = $request->input('file');

            // if ($request->hasFile('file')) {
            //     $file = $request->file('file');
            //     $extension = $file->getClientOriginalExtension();
            //     $filename = time() . '.' . $extension;
            //     $file->move('uploads/file/', $filename);
            //     $payement->file = 'uploads/file/' . $filename;
            // }



            $payement->save();
            return response()->json([
                'status' => 200,
                'message' => 'Payement done Successfully',
            ]);
        }
    }

    public function deleteOldPayments()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $payments = Payement::where('created_at', '<=', $thirtyDaysAgo)->get();

        foreach ($payments as $payment) {
            $payment->delete();
        }

        return response()->json('Old payments deleted successfully.');
    }

    public function minesPayement($id)
    {
        $payement = Payement::find($id);

        if (!$payement) {
            return response()->json([
                'status' => 404,
                'message' => 'Reservation not found',
            ]);
        }

        if ($payement->nb_services > 0) {
            $payement->nb_services -= 1;
            $payement->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Your payement has been -1 successfully',
        ]);
    }
    public function plusPayement($id)
    {
        $payement = Payement::find($id);

        if (!$payement) {
            return response()->json([
                'status' => 404,
                'message' => 'Reservation not found',
            ]);
        }

        $payement->nb_services += 1;
        $payement->save();


        return response()->json([
            'status' => 200,
            'message' => 'Your payement has been +1 successfully',
        ]);
    }
}
