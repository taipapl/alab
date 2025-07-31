<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\Result;
use App\Models\Order;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('imports patient tests from CSV', function () {
    // Przygotuj testowego użytkownika
    $user = User::factory()->create();

    // Przygotuj testowy plik CSV w storage
    $csvContent = <<<CSV
patientId;patientName;patientSurname;patientSex;patientBirthDate;orderId;testName;testValue;testReference
1;Jan;Kowalski;male;1980-01-01;100;Test1;5;1-10
CSV;
    $csvPath = storage_path('app/test.csv');
    file_put_contents($csvPath, $csvContent);

    // Uruchom komendę
    $exitCode = Artisan::call('import:patient-tests-csv', [
        'file' => $csvPath,
    ]);

    expect($exitCode)->toBe(0);

    // Sprawdź, czy rekordy zostały zaimportowane
    expect(Result::count())->toBeGreaterThan(0);
    expect(Order::where('patient_id', 1)->exists())->toBeTrue();

    // Usuń plik testowy
    unlink($csvPath);
});