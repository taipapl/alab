<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Patient;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use Exception;

class ImportPatientTestsCsv extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'import:patient-tests-csv {file}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Imports patient and test data from a CSV file, skipping records without a valid orderId relation.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        // Sprawdź, czy plik istnieje
        if (!file_exists($filePath)) {
            $this->error("Plik CSV nie istnieje pod ścieżką: {$filePath}");
            Log::error("ImportPatientTestsCsv: Plik nie istnieje - {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Rozpoczynanie importu danych z pliku: {$filePath}");

        try {
            // Utwórz czytnik CSV
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0); // Ustaw pierwszy wiersz jako nagłówek

            // Konwertuj iterator na tablicę, aby móc użyć count()
            $records = iterator_to_array($csv->getRecords());
            $importedCount = 0;
            $skippedCount = 0;
            $totalRows = count($records); // Liczba wszystkich wierszy danych (bez nagłówka)

            $this->output->progressStart($totalRows);

            foreach ($records as $offset => $record) {
                $lineNumber = $offset + 2; // +1 dla nagłówka, +1 dla indeksu od 0

                // Mapowanie nazw kolumn z CSV na nazwy pól w bazie danych
                $data = [
                    'patientName'      => $record['patientName'] ?? null,
                    'patientSurname'   => $record['patientSurname'] ?? null,
                    'patientSex'       => strtolower($record['patientSex'] ?? '') === 'male' ? 'male' : (strtolower($record['patientSex'] ?? '') === 'female' ? 'female' : null), // Normalizacja płci
                    'patientBirthDate' => $record['patientBirthDate'] ?? null,
                    'order_id'         => $record['orderId'] ?? null, // Klucz obcy
                    'testName'         => $record['testName'] ?? null,
                    'testValue'        => $record['testValue'] ?? null,
                    'testReference'    => $record['testReference'] ?? null,
                ];

                // --- Walidacja danych i sprawdzenie relacji orderId ---
                $errors = [];

                // Sprawdzenie obecności wymaganych pól
                if (empty($data['patientName'])) $errors[] = 'Brak patientName.';
                if (empty($data['patientSurname'])) $errors[] = 'Brak patientSurname.';
                if (!in_array($data['patientSex'], ['male', 'female'])) $errors[] = 'Nieprawidłowa wartość patientSex (oczekiwano "male" lub "female").';
                if (empty($data['patientBirthDate'])) {
                    $errors[] = 'Brak patientBirthDate.';
                } else {
                    try {
                        // Próba utworzenia obiektu daty w celu walidacji formatu
                        new \DateTime($data['patientBirthDate']);
                    } catch (Exception $e) {
                        $errors[] = 'Nieprawidłowy format daty patientBirthDate.';
                    }
                }
                if (empty($data['order_id'])) $errors[] = 'Brak orderId.';
                if (empty($data['testName'])) $errors[] = 'Brak testName.';
                if (empty($data['testValue'])) $errors[] = 'Brak testValue.';
                if (empty($data['testReference'])) $errors[] = 'Brak testReference.';


                // Sprawdzenie relacji orderId
                $orderExists = false;
                if (!empty($data['order_id'])) {
                    $orderExists = Order::where('orderId', $data['order_id'])->exists();
                    if (!$orderExists) {
                        $errors[] = "Brak powiązania z orderId: {$data['order_id']} w tabeli 'orders'.";
                    }
                }

                if (!empty($errors)) {
                    $skippedCount++;
                    $errorMessage = "\n Pomijanie wiersza {$lineNumber} z powodu błędów: " . implode(', ', $errors) . " Dane: " . json_encode($record);
                    $this->warn($errorMessage);
                    Log::warning("ImportPatientTestsCsv: " . $errorMessage);
                    $this->output->progressAdvance();
                    continue; // Przejdź do następnego wiersza
                }

                // --- Zapis do bazy danych ---
                try {
                    Patient::create($data);
                    $importedCount++;
                } catch (Exception $e) {
                    $skippedCount++;
                    $errorMessage = "Błąd podczas zapisu wiersza {$lineNumber} do bazy danych: " . $e->getMessage() . " Dane: " . json_encode($record);
                    $this->error($errorMessage);
                    Log::error("ImportPatientTestsCsv: " . $errorMessage);
                }
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $this->info("Import zakończony!");
            $this->info("Zaimportowano rekordów: {$importedCount}");
            $this->warn("Pominięto rekordów: {$skippedCount}");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Wystąpił nieoczekiwany błąd podczas importu: " . $e->getMessage());
            Log::critical("ImportPatientTestsCsv: Nieoczekiwany błąd - " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
