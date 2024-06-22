<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(Request $request) {
        $products = Product::all();
        return response()->json([
            'data' => $products
        ], 200);
    }

    public function ShowById($id) {
        $products = Product::where('product_id', $id)->first();

        if($products) {
            return response()->json([
                'data' => $products
            ], 200);
        } else {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function store(Request $request) {
        $validator = Validator::make($request-> all(), [
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'required|string',
            'stock' => 'required|numeric',
            'rating' => 'required|string',
            'category' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $payload = $validator->validated();

        $category = Category::where('category_name', $payload['category'])->first();

        if (!$category) {
            return response()->json([
                'success' => 0,
                'message' => 'Category '.$payload['category']. ' not found'
            ], 404);
        }

        Product::create([
            'product_name' => $payload['product_name'],
            'product_price' => $payload['product_price'],
            'description' => $payload['description'],
            'image' => $payload['image'],
            'category_id' => $category->category_id,
            'stock' => $payload['stock'],
            'rating' => $payload['rating']
        ]);

        return response()->json([
            'success' => 1,
            'message' => 'Product successfully created'
        ], 201);
    }

    public function delete(Request $request, $id) {
        $products = Product::where('product_id', $id)->first();

        if($products) {

            $products->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Product successfully deleted'
            ], 200);
        } else {
            return response()->json([
                'success' => 0,
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request-> all(), [
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'required|string',
            'stock' => 'required|numeric',
            'rating' => 'required|string',
            'category' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $payload = $validator->validated();

        $category = Category::where('category_name', $payload['category'])->first();

        if (!$category) {
            return response()->json([
                'success' => 0,
                'message' => 'Category '.$payload['category']. ' not found'
            ], 404);
        }

        $products = Product::where('product_id', $id)->first();

        if($products) {
            $products->update([
                'product_name' => $payload['product_name'],
                'product_price' => $payload['product_price'],
                'description' => $payload['description'],
                'image' => $payload['image'],
                'category_id' => $category->category_id,
                'stock' => $payload['stock'],
                'rating' => $payload['rating']
            ]);

            return response()->json([
                'success' => 1,
                'message' => 'Product successfully updated'
            ], 200);
        } else {
            return response()->json([
                'success' => 0,
                'message' => 'Product not found'
            ], 404);
        }
    }
}
