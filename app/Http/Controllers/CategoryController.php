<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category; 

class CategoryController extends Controller
{
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $payload = $validator->validated();

        Category::create($payload);

        return response()->json([
            'success' => 1,
            'message' => 'Category successfully created'
        ], 201);
    }

    public function showAll(Request $request) {
        $category = Category::all();
        return response()->json([
            'data' => $category
        ], 200);
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $payload = $validator->validated();

        $category = Category::where('category_id', $id)->first();

        if($category) {
            $category->update([
                'category_name' => $payload['category_name']
            ]);
            return response()->json([
                'success' => 1,
                'message' => 'Category successfully updated'
            ], 200); 

        } else {
            return response()->json([
                'success' => 0,
                'message' => 'Category not found'
            ], 404);
        }
    }

    public function delete(Request $request, $id) {
        $category = Category::where('category_id', $id)->first();

        if($category) {
            $category->delete();
            return response()->json([
                'success' => 1,
                'message' => 'Category successfully deleted'
            ], 200);

        } else {
            return reponse()->json([
                'success' => 0,
                'message' => 'Category not found'
            ], 404);
        }
    }
}
