<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Nexmo\Laravel\Facade\Nexmo;

class ReservationController extends Controller
{
    public function getUserByReservation()
    {
        $user = auth()->user();
        $reservations = Reservation::whereHas('room', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('user_id', '!=', null)->get();

        return response()->json([
            'status' => 200,
            'reservations' => $reservations,
        ]);
    }

    public function getReservationsByRoomIdForP($room_id)
    {
        $reservations = Reservation::where('room_id', $room_id)
            ->orderBy('date', 'asc')
            ->get();
        // if ($reservations->isEmpty()) {
        //     return response()->json(['status' => 404, 'message' => 'Reservations not found for this room id.']);
        // }
        return response()->json(['status' => 200, 'reservations' => $reservations]);
    }


    public function getReservationsByRoomId($room_id)
    {
        $reservations = Reservation::where('room_id', $room_id)
            ->whereNull('user_id')
            ->where('date', '>', now()->format('Y-m-d H:i:s'))
            ->orderBy('date', 'asc')
            ->get();

        return response()->json(['status' => 200, 'reservations' => $reservations]);
    }

    public function countReservationsByRoomId($room_id)
    {
        $count = Reservation::where('room_id', $room_id)
            ->whereNotNull('user_id')
            ->where('date', '>', now()->format('Y-m-d-h'))
            ->count();

        return response()->json(['status' => 200, 'count' => $count]);
    }


    public function getReservationsByRoomIdWithDateNewest($room_id, $reservation_id)
    {

        $reservations = Reservation::where('room_id', $room_id)
            ->whereNotNull('user_id')
            ->where('date', '<', function ($query) use ($reservation_id) {
                $query->select('date')
                    ->from('reservations')
                    ->where('id', $reservation_id);
            })
            ->get();

        return response()->json(['status' => 200, 'reservations' => $reservations]);
    }





    public function reservations()
    {
        $user = auth()->user();
        //worker_id
        $reservations = Reservation::where('user_id', $user->id)->get();

        return response()->json([
            'status' => 200,
            'reservations' => $reservations,
        ]);
    }

    public function StaticReservations()
    {
        $reservations = Reservation::all();
        $totalReservations = $reservations->count();

        return response()->json([
            'status' => 200,
            'totalReservations' => $totalReservations,
        ]);
    }




    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         //'category_id' => 'required|max:191',
    //         //'service_id' => 'required|max:191',
    //         // 'room_id' => 'required|max:191',
    //         //  'worker_id' => 'required|max:191',
    //         //  'user_id' => 'required|max:191',
    //         //  'time' => 'required',
    //         'date' => 'required|date|after_or_equal:now',
    //         //'notification' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 422,
    //             'errors' => $validator->getMessageBag(),
    //         ]);
    //     } else {

    //         $date = $request->input('date');
    //         $timestamp = strtotime($date);
    //         $time = date('H:i:s', $timestamp);

    //         $existingReservations = Reservation::where('date', 'like', substr($date, 0, 10) . '%')
    //             ->get();

    //         foreach ($existingReservations as $existingReservation) {
    //             $existingTime = strtotime($existingReservation->date);
    //             $newTime = strtotime($date);
    //             $timeDifference = abs($newTime - $existingTime) / 60;

    //             if ($timeDifference < 15) {
    //                 return response()->json(
    //                     [
    //                         'status' => 400,
    //                         'errors' =>
    //                         'The selected date is too close to an existing reservation at least 15 minuts',
    //                     ],
    //                 );
    //             }
    //         }


    //         $reservation = new Reservation;
    //         $reservation->category_id = $request->input('category_id');
    //         $reservation->service_id = $request->input('service_id');
    //         $reservation->room_id = $request->input('room_id');
    //         $reservation->worker_id = $request->input('worker_id');
    //         $reservation->user_id = $request->input('user_id');
    //         $reservation->time = $time;
    //         $reservation->date = $date;
    //         $reservation->notification = date('H:i', strtotime($request->input('notification')));

    //         $reservation->save();

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Reservation added successfully',
    //         ]);
    //     }
    // }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'category_id' => 'required|max:191',
            //'service_id' => 'required|max:191',
            // 'room_id' => 'required|max:191',
            //  'user_id' => 'required|max:191',
            //  'time' => 'required',
            'date' => 'required|date|after_or_equal:now',
            'Newrepetition' => 'required|min:1|max:10',
            //'timeDiff' => 'required_if:Newrepetition,>,2',
            //'notification' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $date = $request->input('date');
            $timestamp = strtotime($date);
            $time = date('H:i:s', $timestamp);

            $existingReservations = Reservation::where('date', 'like', substr($date, 0, 10) . '%')
                ->where('room_id', $request->input('room_id'))
                ->get();

            $timeDiff = $request->input('timeDiff'); // New dynamic time difference value

            foreach ($existingReservations as $existingReservation) {
                $existingTime = strtotime($existingReservation->date);
                $newTime = strtotime($date);
                $timeDifference = abs($newTime - $existingTime) / 60;

                if ($timeDifference < $timeDiff) { // Check against dynamic time difference value
                    return response()->json([
                        'status' => 400,
                        'errors' => 'The selected date is too close to an existing reservation. The time difference should be at least ' . $timeDiff . ' minutes.',
                    ]);
                }
            }

            $reservation = new Reservation;
            $reservation->category_id = $request->input('category_id');
            $reservation->service_id = $request->input('service_id');
            $reservation->room_id = $request->input('room_id');
            $reservation->worker_id = $request->input('worker_id');
            $reservation->user_id = $request->input('user_id');
            $reservation->time = date('H:i', strtotime($request->input('time')));
            $reservation->date = $request->input('date');
            $reservation->Newrepetition = $request->input('Newrepetition');
            $reservation->timeDiff = date('H:i', strtotime($request->input('timeDiff')));
            $reservation->notification = date('H:i', strtotime($request->input('notification')));

            $reservation->save();

            return response()->json([
                'status' => 200,
                'message' => 'Reservation added successfully',
            ]);
        }
    }








    public function edit($id)
    {
        $reservation = Reservation::find($id);
        if ($reservation) {
            return response()->json([
                'status' => 200,
                'reservation' => $reservation,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'reservation not found',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            //'category_id' => 'required|max:191',
            //'service_id' => 'required|max:191',
            // 'room_id' => 'required|max:191',
            //  'user_id' => 'required|max:191',
            //  'time' => 'required',
            //'date' => 'required',
            //'notification' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $reservation = Reservation::find($id);
            if ($reservation) {
                $reservation->category_id = $request->input('category_id');
                $reservation->service_id = $request->input('service_id');
                $reservation->room_id = $request->input('room_id');
                $reservation->worker_id = $request->input('worker_id');
                $reservation->user_id = $request->input('user_id');
                $reservation->time = date('H:i', strtotime($request->input('time')));
                $reservation->date = $request->input('date');
                $reservation->notification = date('H:i', strtotime($request->input('notification')));


                $reservation->update();
                return response()->json([
                    'status' => 200,
                    'message' => 'reservation Added Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'reservation Not Found',
                ]);
            }
        }
    }



    public function destroy($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json([
                'status' => 404,
                'message' => 'Reservation not found',
            ]);
        } else {
            $reservation->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Reservation deleted successfully',
            ]);
        }
    }

    public function ResetReservationAdmin($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json([
                'status' => 404,
                'message' => 'Reservation not found',
            ]);
        } else {
            $reservation->user_id = null;
            $reservation->notification = '00:00:00';
            $reservation->save();

            $adresse = $reservation->room->service->adresse;
            $service = $reservation->room->service->name;
            $room = $reservation->room->name;
            $phone = '+216' . $reservation->client->phone;
            $name = $reservation->client->name;
            $date = $reservation->date;

            Nexmo::message()->send([
                'to' => $phone,
                'from' => 'E-Saff APP',
                'text' => "Dear $name, we regret to inform you that your reservation for $service at $room, scheduled for $adresse on $date, has been canceled for unforeseen reasons. We sincerely apologize for any inconvenience caused. If you have any further questions or require assistance, please contact us. Thank you for your understanding."
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Reservation has been canceled successfully',
            ]);
        }
    }


    public function ResetReservation($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json([
                'status' => 404,
                'message' => 'Reservation not found',
            ]);
        } else {
            $reservation->user_id = null;
            $reservation->notification = '00:00:00';
            $reservation->save();

            return response()->json([
                'status' => 200,
                'message' => 'Reservation has been canceled successfully',
            ]);
        }
    }

    // public function EndToNextClientReservation($id)
    // {
    //     $reservation = Reservation::find($id);
    //     if (!$reservation) {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'Reservation not found',
    //         ]);
    //     } else {
    //         $reservation->Newrepetition = null;
    //         $reservation->save();

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Your client reservation has been ended successfully',
    //         ]);
    //     }
    // }
    public function ReservationInProgress($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json([
                'status' => 404,
                'message' => 'Reservation not found',
            ]);
        } else {
            $reservation->Newrepetition = -1;
            $reservation->save();

            return response()->json([
                'status' => 200,
                'message' => 'Your client reservation has been ended successfully',
            ]);
        }
    }

    public function EndToNextClientReservation($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json([
                'status' => 404,
                'message' => 'Reservation not found',
            ]);
        } else {
            $reservation->Newrepetition = null;
            $reservation->save();


            $nextReservation = Reservation::whereNotNull('user_id')
                ->where('date', '<', date('Y-m-d H:i:s'))
                ->where('id', '>', $id)
                ->orderBy('id', 'asc')
                ->first();

            if ($nextReservation) {
                $nextReservation->Newrepetition = -1;
                $nextReservation->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Your client reservation has been ended successfully',
            ]);
        }
    }



    public function UpdateReservation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:now',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $reservation = Reservation::find($id);
            if (!$reservation) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Reservation not found',
                ]);
            } else {
                $reservation->date = $request->input('date');
                $reservation->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Your reservation has been updated successfully',
                ]);
            }
        }
    }

    public function SetTiming(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'time' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $time = $request->input('time');
            $increment = CarbonInterval::createFromFormat('H:i:s', $time);
    
            $reservations = Reservation::where('room_id', $id)->get();
    
            foreach ($reservations as $reservation) {
                $date = Carbon::parse($reservation->date);
                $newDate = $date->add($increment)->toDateTimeString();
    
                $reservation->update([
                    'date' => $newDate,
                ]);
            }
    
            $currentDateTime = now()->format('Y-m-d H:i:s');
    
            $upcomingReservations = Reservation::with('client')
                ->whereNotNull('user_id')
                ->where('date', '>', $currentDateTime)
                ->get();
    
            echo "reservation date: " . $currentDateTime . "\n";
    
            foreach ($upcomingReservations as $reservation) {
                echo "Notification time: " . $currentDateTime . "\n";
                $adresse = $reservation->room->service->adresse;
                $service = $reservation->room->service->name;
                $room = $reservation->room->name;
                echo "reservation ID: " . $reservation->id . "\n";
                echo "client ID: " . $reservation->client->id . "\n";
                echo "client name: " . $reservation->client->name . "\n";
                echo "client phone: " . $reservation->client->phone . "\n";
                $phone = '+216' . $reservation->client->phone;
                $name = $reservation->client->name;
                $dateres = $reservation->date;
                echo " phone:" . $phone . "\n";
    
                Nexmo::message()->send([
                    'to' => $phone,
                    'from' => 'E-Saff APP',
                    'text' =>  "Dear $name, This is an AI E-Saff reminder. Your reservation for $service, $room, at $adresse has been updated. Your new reservation time is adjusted by $time minutes. It is now scheduled for $dateres. Please make sure to arrive accordingly. Thank you!"
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'message' => 'Reservation dates updated successfully',
            ]);
        }
    }
    
    
    public function SetTimingDecrement(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $time = $request->input('time');
            $decrement = CarbonInterval::createFromFormat('H:i:s', $time);

            $reservations = Reservation::where('room_id', $id)->get();

            foreach ($reservations as $reservation) {
                $date = Carbon::parse($reservation->date);
                $date->sub($decrement);

                $reservation->update([
                    'date' => $date,
                ]);
            }

            $currentDateTime = now()->format('Y-m-d H:i:s');

            $upcomingReservations = Reservation::with('client')
                ->whereNotNull('user_id')
                ->where('date', '>', $currentDateTime)
                ->get();

            echo "Reservation date: " . $currentDateTime . "\n";

            foreach ($upcomingReservations as $reservation) {
                echo "Notification time: " . $currentDateTime . "\n";
                $adresse = $reservation->room->service->adresse;
                $service = $reservation->room->service->name;
                $room = $reservation->room->name;
                echo "Reservation ID: " . $reservation->id . "\n";
                echo "Client ID: " . $reservation->client->id . "\n";
                echo "Client name: " . $reservation->client->name . "\n";
                echo "Client phone: " . $reservation->client->phone . "\n";
                $phone = '+216' . $reservation->client->phone;
                $name = $reservation->client->name;
                $dateres = $reservation->date;
                echo "Phone: " . $phone . "\n";

                Nexmo::message()->send([
                    'to' => $phone,
                    'from' => 'E-Saff APP',
                    'text' => "Dear $name, this is an AI E-Saff reminder. Your reservation for $service, $room, on $adresse has been updated. Your new reservation time is adjusted by $time minutes, and it is now scheduled for $dateres. Please make sure to arrive accordingly. Thank you!",
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Reservation dates updated successfully',
            ]);
        }
    }
}
