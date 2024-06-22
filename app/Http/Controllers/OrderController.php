<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Order_Item;
use App\Models\Product;

class OrderController extends Controller
{
    public function create(Request $request) {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:user,user_id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer',
            'order_date' => 'required|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }
    
        $payload = $validator->validated();
    
        $totalAmount = 0;

        foreach ($payload['items'] as $item) {
            $product = Product::find($item['product_id']);
            $totalAmount += $product->product_price * $item['quantity'];
        }
    
        $order = Order::create([
            'user_id' => $payload['user_id'],
            'total_amount' => $totalAmount,
            'order_date' => $payload['order_date'],
            //order_status secara default pending
        ]);
    
        foreach ($payload['items'] as $item) {
            $product = Product::find($item['product_id']);
            Order_Item::create([
                'order_id' => $order->order_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }
    
        return response()->json([
            'success' => 1,
            'message' => 'Order successfully created',
        ], 201);
    }

    public function index() {

        $orders = Order::with('orderItem')->get();

        return response()->json([
            'success' => 1,
            'orders' => $orders,
        ]);
    }
    
    public function showOrderByIdUser($id) {

        $orders = Order::with('orderItem')
                        ->where('user_id', $id)
                        ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => 0,
                'message' => 'Orders not found for this user.',
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'orders' => $orders,
        ]);
    }

    public function updateStatus(Request $request, $id) {

        $validator = Validator::make($request->all(), [
            'order_status' => 'required|in:pending,cancelled,confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $payload = $validator->validated();

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => 0,
                'message' => 'Order not found',
            ], 404);
        }

        $order->update([
            'order_status' => $payload['order_status'],
        ]);

        return response()->json([
            'success' => 1,
            'message' => 'Order status updated successfully.',
        ]);
    }

    public function delete($id) {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => 0,
                'message' => 'Order not found.',
            ], 404);
        }

        $order->delete();

        return response()->json([
            'success' => 1,
            'message' => 'Order deleted successfully.',
        ]);
    }
}
