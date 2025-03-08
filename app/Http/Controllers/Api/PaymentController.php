<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function create(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $order_id = 'INV-' . strtoupper(Str::random(10));
        $amount = $request->amount;

        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $transaction = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ]
        ];

        try {
            $response = Snap::createTransaction($transaction);

            $payment = Payment::create([
                'user_id' => $user->id,
                'order_id' => $order_id,
                'amount' => $amount,
                'status' => 'pending',
                'response' => [
                    'snap_token' => $response->token,
                    'redirect_url' => $response->redirect_url,
                ],
            ]);

            return response()->json([
                'message' => 'Payment created successfully',
                'snap_token' => $response->token,
                'redirect_url' => $response->redirect_url
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Payment creation failed.'], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        $notif = $request->all();

        if (!isset($notif['order_id'])) {
            return response()->json(['message' => 'Invalid notification: order_id missing'], 400);
        }

        $payment = Payment::where('order_id', $notif['order_id'])->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $payment->update([
            'status' => $notif['transaction_status'] ?? 'unknown',
            'payment_type' => $notif['payment_type'] ?? 'unknown',
            'response' => $notif,
        ]);

        return response()->json(['message' => 'Payment status updated']);
    }
}
