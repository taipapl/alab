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

                if (empty($data['name'])) $errors[] = 'Empty patientName.';
                if (empty($data['surname'])) $errors[] = 'Empty patientSurname.';
                if (!in_array($data['sex'], ['male', 'female'])) $errors[] = 'Invalid patientSex value (expected "male" or "female").';
                if (empty($data['birth_date'])) {
                    $errors[] = 'None patientBirthDate.';
                } else {
                    try {
                        new \DateTime($data['birth_date']);
                    } catch (Exception $e) {
                        $errors[] = 'Invalid patientBirthDate format. Expected format: YYYY-MM-DD.';
                    }
                }
                if (empty($data['order_id'])) $errors[] = 'None orderId.';
                if (empty($data['test_name'])) $errors[] = 'None testName.';
                if (empty($data['test_value'])) $errors[] = 'None testValue.';

                $patientExists = false;
                if (!empty($data['patien_id'])) {
                    $patientExists = User::where('id', $data['patien_id'])->exists();
                    if (!$patientExists) {
                        $errors[] = "No relation found for patient_id: {$data['patient_id']} in 'users' table.";
                    }
                }

                $orderExists = false;
                if (!empty($data['order_id'])) {
                    $orderExists = Order::find($data['order_id']);
                    if (!$orderExists) {

                        $order = Order::create([
                            'patient_id' => $data['patient_id'],
                            'order_number' => \Illuminate\Support\Str::uuid()->toString(),
                            'source' => 'import',
                        ]);
                        if ($order) {
                            $this->info("Created new order with ID: {$order->id} for patient ID: {$data['patient_id']}");
                            $data['order_id'] = $order->id;
                        }
                    }
                }

                if (!empty($errors)) {
                    $skippedCount++;
                    $errorMessage = "\n Skip the line {$lineNumber} with reasen: " . implode(', ', $errors) . " Data: " . json_encode($record);
                    $this->warn($errorMessage);
                    Log::warning("ImportPatientTestsCsv: " . $errorMessage);
                    $this->output->progressAdvance();
                    continue;
                }

                try {
                    Result::create(Arr::only($data, ['patient_id', 'order_id', 'test_name', 'test_value', 'test_reference']));
                    $importedCount++;
                } catch (Exception $e) {
                    $skippedCount++;
                    $errorMessage = "\n Error on line {$lineNumber}: " . $e->getMessage() . " Data: " . json_encode($record);
                    $this->error($errorMessage);
                    Log::error("ImportPatientTestsCsv: " . $errorMessage);
                }
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $this->info("Import completed successfully.");
            $this->info("Imported records: {$importedCount}");
            $this->warn("Skipped records: {$skippedCount}");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("An unexpected error occurred: " . $e->getMessage());
            Log::critical("ImportPatientTestsCsv: An unexpected error - " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}