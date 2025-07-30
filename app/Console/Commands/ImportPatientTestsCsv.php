<?php

namespace App\Console\Commands;

use Exception;
use App\Models\User;
use App\Models\Order;
use App\Models\Result;
use League\Csv\Reader;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

        if (!file_exists($filePath)) {
            $this->error("File does not exist: {$filePath}");
            Log::error("ImportPatientTestsCsv: File does not exist: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Importing data from file: {$filePath}");

        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setDelimiter(';'); // Dodano separator średnika
            $csv->setHeaderOffset(0);

            $records = iterator_to_array($csv->getRecords());

            $importedCount = 0;
            $skippedCount = 0;
            $totalRows = count($records);

            $this->output->progressStart($totalRows);

            foreach ($records as $offset => $record) {

                $lineNumber = $offset + 2;

                $data = [
                    'patient_id' => $record['patientId'] ?? null,
                    'name'      => $record['patientName'] ?? null,
                    'surname'   => $record['patientSurname'] ?? null,
                    'sex'       => strtolower($record['patientSex'] ?? '') === 'male' ? 'male' : (strtolower($record['patientSex'] ?? '') === 'female' ? 'female' : null), // Normalizacja płci
                    'birth_date' => $record['patientBirthDate'] ?? null,
                    'order_id'         => $record['orderId'] ?? null,
                    'test_name'         => $record['testName'] ?? null,
                    'test_value'        => $record['testValue'] ?? null,
                    'test_reference'    => $record['testReference'] ?? null,
                ];

                $errors = [];

                if (empty($data['name'])) $errors[] = 'Brak patientName.';
                if (empty($data['surname'])) $errors[] = 'Brak patientSurname.';
                if (!in_array($data['sex'], ['male', 'female'])) $errors[] = 'Nieprawidłowa wartość patientSex (oczekiwano "male" lub "female").';
                if (empty($data['birth_date'])) {
                    $errors[] = 'Brak patientBirthDate.';
                } else {
                    try {
                        new \DateTime($data['birth_date']);
                    } catch (Exception $e) {
                        $errors[] = 'Nieprawidłowy format daty patientBirthDate.';
                    }
                }
                if (empty($data['order_id'])) $errors[] = 'Brak orderId.';
                if (empty($data['test_name'])) $errors[] = 'Brak testName.';
                if (empty($data['test_value'])) $errors[] = 'Brak testValue.';

                $orderExists = false;
                if (!empty($data['order_id'])) {
                    $orderExists = Order::where('id', $data['order_id'])->exists();
                    if (!$orderExists) {
                        $errors[] = "Brak powiązania z order_id: {$data['order_id']} w tabeli 'orders'.";
                    }
                }

                $patientExists = false;
                if (!empty($data['patien_id'])) {
                    $patientExists = User::where('id', $data['patien_id'])->exists();
                    if (!$patientExists) {
                        $errors[] = "Brak powiązania z patient_id: {$data['patient_id']} w tabeli 'users'.";
                    }
                }

                if (!empty($errors)) {
                    $skippedCount++;
                    $errorMessage = "\n Pomijanie wiersza {$lineNumber} z powodu błędów: " . implode(', ', $errors) . " Dane: " . json_encode($record);
                    $this->warn($errorMessage);
                    Log::warning("ImportPatientTestsCsv: " . $errorMessage);
                    $this->output->progressAdvance();
                    continue;
                }

                //dd(Arr::only($data, ['patient_id', 'order_id', 'test_name', 'test_value', 'test_reference']));
                try {
                    Result::create(Arr::only($data, ['patient_id', 'order_id', 'test_name', 'test_value', 'test_reference']));
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