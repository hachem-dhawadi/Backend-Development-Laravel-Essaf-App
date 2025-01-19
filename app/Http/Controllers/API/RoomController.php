<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class RoomController extends Controller
{
    public function allroomsbyuser()
    {
        $user = auth()->user();
        $room = Room::where('user_id', $user->id)->get();

        return response()->json([
            'status' => 200,
            'rooms' => $room,
        ]);
    }
    public function allrooms()
    {
        $rooms = Room::all();
        return response()->json([
            'status' => 200,
            'rooms' => $rooms,
        ]);
    }
    public function getRoomById($id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json(['status' => 404, 'message' => 'Room not found']);
        }
        return response()->json(['status' => 200, 'room' => $room]);
    }


    public function roomserv($serviceId) //fetch rooms by services
    {
        $rooms = Room::where('service_id', $serviceId)->get();
        return response()->json([
            'status' => 200,
            'rooms' => $rooms
        ]);
    }

    public function StaticRoom()
    {
        $rooms = Room::all();
        $totalRooms = $rooms->count();

        $activeRooms = $rooms->where('active', 1)->count();
        $inactiveRooms = $rooms->where('active', 0)->count();

        return response()->json([
            'status' => 200,
            'totalRooms' => $totalRooms,
            'activeRooms' => $activeRooms,
            'inactiveRooms' => $inactiveRooms,
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|max:191',
            'service_id' => 'required|max:191',
            'start' => 'required|date|after_or_equal:now',
            'end' => 'required|date|after:start',
            'name' => 'required|max:191|unique:services,name',
            'image' => 'mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG,pdf,docx',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $room = new Room;
            $room->user_id = $request->input('user_id');
            $room->service_id = $request->input('service_id');
            $room->start = $request->input('start');
            $room->end = $request->input('end');
            //$room->end = date('H:i', strtotime($request->input('end')));
            $room->name = $request->input('name');
            $room->description = $request->input('description');
            $room->active = $request->input('active');

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/room/', $filename);
                $room->image = 'uploads/room/' . $filename;
            }

            $room->save();
            return response()->json([
                'status' => 200,
                'message' => 'Room Added Successfully',
            ]);
        }
    }

    public function edit($id)
    {
        $room = Room::find($id);
        if ($room) {
            return response()->json([
                'status' => 200,
                'room' => $room,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Service not found',
            ]);
        }
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|max:191',
            'service_id' => 'required|max:191',
            'start' => 'required',
            'end' => 'required',
            'name' => 'required|max:191|unique:services,name',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $room = Room::find($id);
            if ($room) {

                $room->user_id = $request->input('user_id');
                $room->service_id = $request->input('service_id');
                $room->start = $request->input('start');
                $room->end = $request->input('end');
                //$room->end = date('H:i', strtotime($request->input('end')));
                $room->name = $request->input('name');
                $room->description = $request->input('description');
                $room->active = $request->input('active');
                if ($request->hasFile('image')) {
                    $path = $room->image;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                    $file = $request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $file->move('uploads/room/', $filename);
                    $room->image = 'uploads/room/' . $filename;
                }

                $room->update();
                return response()->json([
                    'status' => 200,
                    'message' => 'Room Updated Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                    'message' => 'Room Not Found',
                ]);
            }
        }
    }

    public function destroy($id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json([
                'status' => 404,
                'message' => 'room not found',
            ]);
        }
        $room->delete();
        return response()->json([
            'status' => 200,
            'message' => 'room deleted successfully',
        ]);
    }
}
