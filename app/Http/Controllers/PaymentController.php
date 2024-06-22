<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Request $request)
    {
        $orderId = uniqid();
        $grossAmount = $request->input('gross_amount');
        $items = $request->input('items');
        $customerDetails = $request->input('customer_details'); // array of customer details

        // Log data yang diterima
        Log::info('Received data:', ['gross_amount' => $grossAmount, 'items' => $items, 'customer_details' => $customerDetails]);

        if (!is_array($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Items should be an array'
            ], 400);
        }

        foreach ($items as $item) {
            if (!isset($item['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Undefined array key "id" in one of the items'
                ], 400);
            }
        }

        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
        ];

        $transaction = [
            'transaction_details' => $transactionDetails,
            'item_details' => $items,
            'customer_details' => $customerDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($transaction);
            return response()->json([
                'success' => true,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Webhook received:', $request->all());

        $requestData = $request->all();
        Log::info('Request Data:', $requestData);

        try {
            $orderId = $requestData['order_id'] ?? null;
            $transactionStatus = $requestData['transaction_status'] ?? null;
            $fraudStatus = $requestData['fraud_status'] ?? null;

            if (!$orderId || !$transactionStatus || !$fraudStatus) {
                Log::error('Missing required fields', [
                    'order_id' => $orderId,
                    'transaction_status' => $transactionStatus,
                    'fraud_status' => $fraudStatus,
                ]);
                return response()->json(['message' => 'Missing required fields'], 400);
            }

            $order = Order::where('order_id', $orderId)->first();

            if (!$order) {
                Log::error('Order not found', ['order_id' => $orderId]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            switch ($transactionStatus) {
                case 'capture':
                    $order->order_status = ($fraudStatus == 'challenge') ? 'challenge' : 'success';
                    break;
                case 'settlement':
                    $order->order_status = 'success';
                    break;
                case 'pending':
                    $order->order_status = 'pending';
                    break;
                case 'deny':
                    $order->order_status = 'deny';
                    break;
                case 'expire':
                    $order->order_status = 'expire';
                    break;
                case 'cancel':
                    $order->order_status = 'cancel';
                    break;
                default:
                    Log::error('Unknown transaction status', ['transaction_status' => $transactionStatus]);
                    return response()->json(['message' => 'Unknown transaction status'], 400);
            }

            $order->save();

            return response()->json(['message' => 'Notification handled successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error processing notification', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }
}
