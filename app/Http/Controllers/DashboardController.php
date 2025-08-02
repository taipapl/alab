<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('results');

        return Inertia::render('Dashboard', [
            'status' => $request->session()->get('status'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
            ],
            'results' => $user->results->map(function ($result) {
                return [
                    'test_name' => $result->test_name,
                    'test_value' => $result->test_value,
                    'test_reference' => $result->test_reference,
                    'created_at' => $result->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }
}
