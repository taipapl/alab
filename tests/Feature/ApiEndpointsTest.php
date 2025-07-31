<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Result;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('logs in user and returns JWT token', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
        'password' => bcrypt('secret'),
    ]);

    $response = $this->postJson('/api/login', [
        'username' => 'testuser',
        'password' => 'secret',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token']);
});

test('returns 401 for invalid credentials', function () {
    $response = $this->postJson('/api/login', [
        'username' => 'invaliduser',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['error' => 'Invalid credentials']);
});

test('returns patient data and results for authenticated user', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
        'password' => bcrypt('secret'),
        'sex' => 'male',
        'birth_date' => '2000-01-01',
    ]);
    $order = Order::factory()->create(['patient_id' => $user->id]);
    Result::factory()->create([
        'order_id' => $order->id,
        'test_name' => 'foo',
        'test_value' => '1',
        'test_reference' => '1-2',
        'patient_id' => $user->id,
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->getJson('/api/results', [
        'Authorization' => "Bearer $token",
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'patient' => ['id', 'name', 'surname', 'sex', 'birthDate'],
            'orders' => [
                ['orderId', 'results' => [['name', 'value', 'reference']]]
            ]
        ]);
});

test('returns 401 for unauthenticated user', function () {
    $response = $this->getJson('/api/results');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated']);
});