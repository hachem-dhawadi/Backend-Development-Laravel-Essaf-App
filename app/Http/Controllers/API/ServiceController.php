<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{

    public function allservicesbyuser()
    {
        $user = auth()->user();
        $services = Service::where('user_id', $user->id)->get();

        return response()->json([
            'status' => 200,
            'services' => $services,
        ]);
    }
    public function catserv($categoryId) //fetch services by category
    {
        $services = Service::where('category_id', $categoryId)->get();
        return response()->json([
            'status' => 200,
            'services' => $services
        ]);
    }

    public function StaticServices()
    {
        $services = Service::all();
        $totalServices = $services->count();

        $activeServices = $services->where('active', 1)->count();
        $inactiveServices = $services->where('active', 0)->count();

        return response()->json([
            'status' => 200,
            'totalServices' => $totalServices,
            'activeServices' => $activeServices,
            'inactiveServices' => $inactiveServices,
        ]);
    }


    public function allservices()
    {
        $services = Service::all();
        return response()->json([
            'status' => 200,
            'services' => $services,
        ]);
    }
    public function nbservices()
    {
        $countservices = DB::table('services')->count();
        return response()->json([
            'status' => 200,
            'countservices' => $countservices,
        ]);
    }
    public function nb_serv_by_category()
    {
        $countservices = DB::table('services')
            ->select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->get();

        return response()->json([
            'status' => 200,
            'countservices' => $countservices,
        ]);
    }


    /*
        public function allservices(Request $request)
    {
      $category = $request->query('category');
      if ($category) {
        $services = Service::where('category', $category)->get();
      } else {
        $services = Service::all();
      }
      return response()->json([
        'status'=>200,
        'services'=>$services,
      ]);
    }
    */


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|max:191',
            'user_id' => 'required|max:191',
            //'start' =>'required',
            'name' => 'required|max:191|unique:services,name',
            'location' => 'required|max:191',
            'adresse' => 'required|max:191',
            //'image' =>'required|image|mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG|unique:services,image',
            'image' => 'mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $service = new Service;
            $service->category_id = $request->input('category_id');
            $service->user_id = $request->input('user_id');
            $service->start = $request->input('start');
            $service->name = $request->input('name');
            $service->location = $request->input('location');
            $service->adresse = $request->input('adresse');
            $service->description = $request->input('description');
            $service->active = $request->input('active');

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/service/', $filename);
                $service->image = 'uploads/service/' . $filename;
            }
            $service->save();
            return response()->json([
                'status' => 200,
                'message' => 'Service Added Successfully',
            ]);
        }
    }


    public function edit($id)
    {
        $service = Service::find($id);
        if ($service) {
            return response()->json([
                'status' => 200,
                'service' => $service,
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
            //'user_id' =>'required|max:191',
            'name' => 'required|max:191',
            //'location' =>'required|max:191',
            'adresse' => 'required|max:191',
            //'image' =>'required|image|mimes:jpeg,png,jpg',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $service = Service::find($id);
            if ($service) {
                $service->category_id = $request->input('category_id');
                $service->user_id = $request->input('user_id');
                $service->name = $request->input('name');
                $service->location = $request->input('location');
                $service->adresse = $request->input('adresse');
                $service->description = $request->input('description');
                $service->active = $request->input('active');

                if ($request->hasFile('image')) {
                    $path = $service->image;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                    $file = $request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $file->move('uploads/service/', $filename);
                    $service->image = 'uploads/service/' . $filename;
                }
                $service->update();
                return response()->json([
                    'status' => 200,
                    'message' => 'Service Updated Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Service Not Found',
                ]);
            }
        }
    }
    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json([
                'status' => 404,
                'message' => 'service not found',
            ]);
        }
        $service->delete();
        return response()->json([
            'status' => 200,
            'message' => 'service deleted successfully',
        ]);
    }
}
