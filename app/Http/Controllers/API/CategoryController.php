<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function allcategories()
    {
        $categories = Category::all();
        return response()->json([
            'status' => 200,
            'categories' => $categories,
        ]);
    }

    public function StaticCategorie()
    {
        $categories = Category::all();
        $totalCategories = $categories->count();

        $activeCategories = $categories->where('active', 1)->count();
        $inactiveCategories = $categories->where('active', 0)->count();

        return response()->json([
            'status' => 200,
            'totalCategories' => $totalCategories,
            'activeCategories' => $activeCategories,
            'inactiveCategories' => $inactiveCategories,
        ]);
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191|unique:categories,name',
            //'adresse' =>'required|max:191',
            //'image' =>'required|image|mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG|unique:categorys,image',
            'image' => 'mimes:jpeg,png,jpg,jfif,pjpeg,pjp,svg,PNG,JPEG,JPG',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $category = new Category;
            $category->name = $request->input('name');
            $category->description = $request->input('description');
            $category->active = $request->input('active');


            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/category/', $filename);
                $category->image = 'uploads/category/' . $filename;
            }

            $category->save();
            return response()->json([
                'status' => 200,
                'message' => 'Category Added Successfully',
            ]);
        }
    }

    public function edit($id)
    {
        $category = Category::find($id);
        if ($category) {
            return response()->json([
                'status' => 200,
                'category' => $category,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'category not found',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            //'user_id' =>'required|max:191',
            'name' => 'required|max:191',
            //'location' =>'required|max:191',
            //'adresse' =>'required|max:191',
            //'image' =>'required|image|mimes:jpeg,png,jpg',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->getMessageBag(),
            ]);
        } else {
            $category = Category::find($id);
            if ($category) {
                $category->name = $request->input('name');
                $category->description = $request->input('description');
                $category->active = $request->input('active');

                if ($request->hasFile('image')) {
                    $path = $category->image;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                    $file = $request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $file->move('uploads/category/', $filename);
                    $category->image = 'uploads/category/' . $filename;
                }
                $category->update();
                return response()->json([
                    'status' => 200,
                    'message' => 'category Updated Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'category Not Found',
                ]);
            }
        }
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'category not found',
            ]);
        }
        $category->delete();
        return response()->json([
            'status' => 200,
            'message' => 'category deleted successfully',
        ]);
    }
}
