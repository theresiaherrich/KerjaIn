<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Program;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['notification']]);
        $this->middleware('admin')->only(['index', 'update', 'destroy']);

    }

    public function index()
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();
            $payments = Payment::orderBy('created_at', 'desc')->get();
            return response()->json(['payments' => $payments]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();

            $payment = Payment::find($id);

            if (!$payment) {
                return response()->json(['error' => 'Payment not found.'], 404);
            }

            $request->validate([
                'status' => 'required|string',
                'payment_type' => 'nullable|string'
            ]);

            $payment->update([
                'status' => $request->status,
                'payment_type' => $request->payment_type ?? $payment->payment_type,
            ]);

            return response()->json(['message' => 'Payment updated successfully', 'payment' => $payment]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();

            $payment = Payment::find($id);

            if (!$payment) {
                return response()->json(['error' => 'Payment not found.'], 404);
            }

            $payment->delete();

            return response()->json(['message' => 'Payment deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {
        try{
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
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }

    public function notification(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            $notification = new \Midtrans\Notification();
            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $paymentType = $notification->payment_type;

            $payment = Payment::where('order_id', $orderId)->first();
            if (!$payment) {
                Log::error("Payment with order_id $orderId not found.");
                return response()->json(['error' => 'Payment not found.'], 404);
            }

            $statusMapping = [
                'settlement' => 'paid',
                'capture' => 'paid',
                'pending' => 'pending',
                'deny' => 'failed',
                'cancel' => 'canceled',
                'expire' => 'expired',
                'refund' => 'refunded'
            ];
            $newStatus = $statusMapping[$transactionStatus] ?? 'unknown';

            $payment->update([
                'status' => $newStatus,
                'payment_type' => $paymentType,
                'response' => json_encode($request->all())
            ]);

            Log::info("Payment updated successfully for order_id $orderId with status $newStatus.");

            $responseMessages = [
                'paid' => 'Payment successful.',
                'pending' => 'Payment is pending. Please wait for confirmation.',
                'failed' => 'Payment failed. Please try again.',
                'canceled' => 'Payment has been canceled.',
                'expired' => 'Payment has expired. Please make a new transaction.',
                'refunded' => 'Payment has been refunded.',
                'unknown' => 'Payment status is unknown. Please contact support.'
            ];

            return response()->json([
                'message' => $responseMessages[$newStatus] ?? 'Payment status updated.'
            ]);
        } catch (\Exception $e) {
            Log::error("Midtrans Notification Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to process notification.'], 500);
        }
    }

    public function history()
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();

            $payments = Payment::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([ 'payments' => $payments ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}

