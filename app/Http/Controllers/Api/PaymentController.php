<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Program;
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

        $request->validate([
            'phone' => 'required|string|max:15',
            'voucher_code' => 'nullable|string|max:20',
        ]);

        if (!$user->selected_program_id) {
            Log::error('User ' . $user->id . ' has no selected program.');
            return response()->json(['error' => 'No program selected.'], 400);
        }

        $program = Program::find($user->selected_program_id);

        if (!$program) {
            Log::error('Program ID ' . $user->selected_program_id . ' not found.');
            return response()->json(['error' => 'Program not found.'], 404);
        }

        $order_id = 'INV-' . strtoupper(Str::random(10));

        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $transaction = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $program->price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $request->phone,
            ],
        ];

        try {
            $response = Snap::createTransaction($transaction);

            Payment::create([
                'user_id' => $user->id,
                'program_id' => $program->id,
                'order_id' => $order_id,
                'amount' => $program->price,
                'phone' => $request->phone,
                'voucher_code' => $request->voucher_code,
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

    public function history()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $payments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([ 'payments' => $payments ]);
    }

}

