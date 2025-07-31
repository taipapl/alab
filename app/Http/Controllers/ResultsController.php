<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Result;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $orders = Order::where('patient_id', $user->id)->get()->map(function ($order) {
            return [
                'orderId' => $order->id,
                'results' => Result::where('order_id', $order->id)->get()->map(function ($result) {
                    return [
                        'name' => $result->name,
                        'value' => $result->value,
                        'reference' => $result->reference,
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