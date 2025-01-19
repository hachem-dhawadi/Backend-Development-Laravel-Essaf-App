<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendEmailJob;
use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{
    public function alljobsbyowner()
    {
        $user = auth()->user();
        $jobs = Job::where('reciver_id', $user->id)
            ->where('active', 1)
            ->get();
        return response()->json([
            'status' => 200,
            'jobs' => $jobs,
        ]);
    }


    public function alljobsbyauth()
    {
        $user = auth()->user();
        $jobs = Job::where('sender_id', $user->id)
            ->where('active', 1)
            ->get();
        return response()->json([
            'status' => 200,
            'jobs' => $jobs,
        ]);
    }


    public function alljobs()
    {
        $jobs = Job::all();
        return response()->json([
            'status' => 200,
            'jobs' => $jobs,
        ]);
    }

    public function jobbyid($id)
    {
        $job = Job::where('id', $id)->first();
        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Job not found'
            ]);
        }

        return response()->json([
            'status' => 200,
            'job' => $job,
        ]);
    }


    public function alljobsbyusersender()
    {
        $user = auth()->user();
        $job = Job::where('sender_id', $user->id)
            ->where('active', 0)
            ->get();

        return response()->json([
            'status' => 200,
            'jobs' => $job,
        ]);
    }
    public function alljobsbyuserreciver()
    {
        $user = auth()->user();
        $jobs = Job::where('reciver_id', $user->id)
            ->where('gnot', '=', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 200,
            'jobs' => $jobs,
        ]);
    }

    public function StaticJobs()
    {
        $jobs = Job::all();
        $totalJobs = $jobs->count();

        return response()->json([
            'status' => 200,
            'totalJobs' => $totalJobs,
        ]);
    }




    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'sender_id' =>'required|max:191',
            //'reciver_id' =>'required|max:191',
            'description' => 'required',
            //'image' =>'required|image|mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG|unique:jobs,image',
            'file' => 'mimes:pdf,docx',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $job = new job;
            $job->room_id = $request->input('room_id');
            $job->sender_id = $request->input('sender_id');
            $job->reciver_id = $request->input('reciver_id');
            $job->description = $request->input('description');
            $job->file = $request->input('file');
            $job->active = $request->input('active');
            $job->gnot = $request->input('gnot');
            $job->anot = $request->input('anot');

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/file/', $filename);
                $job->file = 'uploads/file/' . $filename;
                /*$file = $request->file('service');
                $filename = $file->getClientOriginalName();
                $path = $file->store('uploads/file');
                $user->service = $path;*/
            }



            $job->save();
            return response()->json([
                'status' => 200,
                'message' => 'job send it Successfully',
            ]);
        }
    }

    public function edit($id)
    {
        $job = Job::find($id);
        if ($job) {
            return response()->json([
                'status' => 200,
                'job' => $job,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'job not found',
            ]);
        }
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            //'sender_id' =>'required|max:191',
            //'reciver_id' =>'required|max:191',
            //'description' =>'required',
            //'image' =>'required|image|mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG|unique:jobs,image',
            //'file' =>'mimes:pdf,docx',   
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $job = Job::find($id);
            if ($job) {
                $job->room_id = $request->input('room_id');
                $job->sender_id = $request->input('sender_id');
                $job->reciver_id = $request->input('reciver_id');
                $job->description = $request->input('description');
                $job->file = $request->input('file');
                $job->active = $request->input('active');
                $job->gnot = $request->input('gnot');
                $job->anot = $request->input('anot');

                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $file->move('uploads/file/', $filename);
                    $job->file = 'uploads/file/' . $filename;
                    /*$file = $request->file('service');
                $filename = $file->getClientOriginalName();
                $path = $file->store('uploads/file');
                $user->service = $path;*/
                }
                Mail::to('hachemdhawadi2001@gmail.com')->send(
                    new SendEmailJob('hallloooooooo')
                );
                $job->update();
                return response()->json([
                    'status' => 200,
                    'message' => 'job Done Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'job Not Found',
                ]);
            }
        }
    }

    public function destroy($id)
    {
        $job = Job::find($id);
        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'job not found',
            ]);
        }
        $job->delete();
        return response()->json([
            'status' => 200,
            'message' => 'job deleted successfully',
        ]);
    }


    public function ResetJob($id)
    {
        $job = Job::find($id);
        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Reservation not found',
            ]);
        } else {
            $job->active = 0;
            $job->save();

            return response()->json([
                'status' => 200,
                'message' => 'Your User has been fired successfully',
            ]);
        }
    }

    
    public function sendemailJob(Request $request)
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
                new SendEmailJob('hallloooooooo')
            );

            return response()->json([
                'message' => 'Email sent successfully',
                'status' => 200,
            ]);
        }
    }
}
