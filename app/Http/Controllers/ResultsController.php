<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $orders = Order::where('patient_id', $user->id)->get()->map(function ($order) {
            return [
                'orderId' => $order->id,
                'results' => Result::where('order_id', $order->id)
                    ->orderBy('created_at', 'desc')
                    ->get()->map(function ($result) {
                        return [
                            'name' => $result->test_name,
                            'value' => $result->test_value,
                            'reference' => $result->test_reference,
                        ];
                    }),
            ];
        });

        return response()->json([
            'patient' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'sex' => $user->sex,
                'birthDate' => $user->birth_date,
            ],
            'orders' => $orders,
        ]);
    }
}
