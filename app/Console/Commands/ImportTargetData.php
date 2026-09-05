<?php

namespace App\Console\Commands;

use App\Models\Manpower;
use App\Models\Target;
use App\Models\TargetItem;
use App\Models\Achievement;
use App\Models\Whitelist;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ImportTargetData extends Command
{
    protected $signature = 'import:target-data';
    protected $description = 'Import manpower, targets, and achievements from CSV files';

    public function handle(): int
    {
        Carbon::setLocale('id');

        $this->info('Parsing data.csv...');
        $manpowerData = $this->parseManpowerCsv();
        $this->info('  Found ' . count($manpowerData) . ' riders');

        $this->info('Parsing target.csv...');
        $weekBlocks = $this->parseTargetCsv();
        $this->info('  Found ' . count($weekBlocks) . ' weeks');

        $this->info('Creating manpower records...');
        $manpowerMap = $this->createManpowerRecords($manpowerData);

        $this->info('Creating targets and achievements...');
        $this->createTargetRecords($weekBlocks, $manpowerMap);

        $this->info('Done!');
        return 0;
    }

    private function parseManpowerCsv(): array
    {
        $path = base_path('data/data.csv');
        $handle = fopen($path, 'r');

        // Skip header
        fgetcsv($handle, 0, "\t");

        $riders = [];
        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (count($row) < 4) continue;

            $driver = trim($row[0]);
            $vehicleRaw = trim($row[2]);
            $contractRaw = trim($row[3]);

            // Extract NIP and name from "[NIP]Name" format
            if (!preg_match('/^\[(\d+)\](.+)$/', $driver, $m)) continue;

            $nip = $m[1];
            $name = trim($m[2]);

            // Map vehicle type
            if (str_contains($vehicleRaw, '2WH')) {
                $vehicleType = '2wh';
            } elseif (str_contains($vehicleRaw, '4WH')) {
                $vehicleType = '4wh';
            } else {
                continue;
            }

            // Map contract type
            if (str_contains($contractRaw, 'DEDICATED')) {
                $contractType = 'dedicated';
            } elseif (str_contains($contractRaw, 'MITRA')) {
                $contractType = 'mitra';
            } else {
                continue;
            }

            $riders[] = [
                'nip' => $nip,
                'name' => $name,
                'vehicle_type' => $vehicleType,
                'contract_type' => $contractType,
            ];
        }

        fclose($handle);
        return $riders;
    }

    private function parseTargetCsv(): array
    {
        $path = base_path('data/target.csv');
        $lines = file($path, FILE_IGNORE_NEW_LINES);

        $weeks = [];
        $state = 'idle';
        $currentWeek = null;
        $headerBuffer = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (str_contains($trimmed, 'PRODUCTIVITY by DELIVERED')) {
                // Save previous week if exists
                if ($currentWeek && !empty($currentWeek['rows'])) {
                    $weeks[] = $currentWeek;
                }
                $state = 'header';
                $headerBuffer = '';
                $currentWeek = null;
                continue;
            }

            if ($state === 'header') {
                $headerBuffer .= ' ' . $trimmed;

                // Try to extract week number and dates
                if (preg_match_all('/(\d{1,2}\s+[A-Z][a-z]{2})/', $headerBuffer, $dateMatches)) {
                        $dates = $dateMatches[1];

                        // Calculate actual ISO week number from first date
                        $firstDateStr = $dates[0] . ' ' . Carbon::now()->year;
                        $firstDate = Carbon::parse($firstDateStr);
                        $weekNumber = $firstDate->weekOfYear;

                        $currentWeek = [
                            'week_number' => $weekNumber,
                            'dates' => $dates,
                            'rows' => [],
                        ];
                        $state = 'data';
                    }
                continue;
            }

            if ($state === 'data') {
                if (empty($trimmed)) {
                    // Empty line - end of week block
                    if ($currentWeek && !empty($currentWeek['rows'])) {
                        $weeks[] = $currentWeek;
                        $currentWeek = null;
                    }
                    $state = 'idle';
                } else {
                    $currentWeek['rows'][] = $trimmed;
                }
            }
        }

        // Don't forget the last week
        if ($currentWeek && !empty($currentWeek['rows'])) {
            $weeks[] = $currentWeek;
        }

        return $weeks;
    }

    private function createManpowerRecords(array $manpowerData): array
    {
        $map = []; // name (uppercase) => Manpower

        // First pass: create all manpower, prefer DEDICATED over MITRA for name mapping
        foreach ($manpowerData as $data) {
            $manpower = Manpower::updateOrCreate(
                ['nip' => $data['nip']],
                [
                    'full_name' => $data['name'],
                    'vehicle_type' => $data['vehicle_type'],
                    'contract_type' => $data['contract_type'],
                    'is_active' => true,
                    'start_date' => Carbon::now()->subYear(),
                ]
            );

            $nameKey = strtoupper($data['name']);

            // Prefer DEDICATED over MITRA for the name map
            if (!isset($map[$nameKey]) || $data['contract_type'] === 'dedicated') {
                $map[$nameKey] = $manpower;
            }
        }

        return $map;
    }

    private function createTargetRecords(array $weekBlocks, array $manpowerMap): void
    {
        $seenWeeks = [];

        foreach ($weekBlocks as $block) {
            $weekNumber = $block['week_number'];
            $year = Carbon::now()->year;

            // Skip duplicate weeks
            if (isset($seenWeeks[$weekNumber])) {
                $this->warn("  Skipping duplicate Week {$weekNumber}");
                continue;
            }
            $seenWeeks[$weekNumber] = true;

            $startDate = Target::calcStartDate($year, $weekNumber);
            $endDate = Target::calcEndDate($year, $weekNumber);

            $this->info("  Week {$weekNumber}: {$startDate->format('d M')} - {$endDate->format('d M')}");

            // Create Target record
            $target = Target::updateOrCreate(
                [
                    'year' => $year,
                    'week_number' => $weekNumber,
                ],
                [
                    'name' => "Minggu {$weekNumber}",
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'apply_all_days' => true,
                ]
            );

            // Parse each rider's data
            foreach ($block['rows'] as $row) {
                $this->parseAndCreateRiderData($row, $target, $startDate, $manpowerMap);
            }
        }
    }

    private function parseAndCreateRiderData(string $row, Target $target, Carbon $weekStart, array $manpowerMap): void
    {
        $cols = explode("\t", $row);
        if (count($cols) < 3) return;

        $name = trim($cols[0]);

        // Skip non-rider rows (AVERAGE, empty, etc.)
        if ($name === '' || $name === 'AVERAGE' || $name === 'RIDER NAME' || $name === 'DRIVER NAME') return;

        $dailyTarget = (int) trim($cols[1]);
        $weeklyTarget = (int) trim($cols[2]);

        // Pad cols to ensure 10 elements (name + 2 targets + 7 days)
        while (count($cols) < 10) {
            $cols[] = '';
        }

        // Find matching manpower
        $manpower = $manpowerMap[strtoupper($name)] ?? null;
        if (!$manpower) {
            $this->warn("    Manpower not found: {$name}");
            return;
        }

        // Create TargetItem
        TargetItem::updateOrCreate(
            [
                'target_id' => $target->id,
                'manpower_id' => $manpower->id,
            ],
            [
                'daily_target' => $dailyTarget,
                'weekly_target' => $weeklyTarget,
            ]
        );

        // Create Achievement records for each day (columns 3-9)
        // First empty day per rider per week = Libur (day off)
        // Subsequent empty days = Izin (permission)
        $firstEmpty = true;

        for ($day = 0; $day < 7; $day++) {
            $colIndex = 3 + $day;
            if (!isset($cols[$colIndex])) continue;

            $value = trim($cols[$colIndex]);
            $date = $weekStart->copy()->addDays($day);

            // Empty or "-" = no delivery → whitelist
            if ($value === '' || $value === '-') {
                $reason = $firstEmpty ? 'Libur' : 'Izin';
                Whitelist::updateOrCreate(
                    [
                        'manpower_id' => $manpower->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'reason' => $reason,
                        'created_by' => null,
                    ]
                );
                $firstEmpty = false;
                continue;
            }

            // Handle values with commas (e.g., "1,331")
            $achievementValue = (int) str_replace(',', '', $value);

            Achievement::saveWithCarryover(
                $manpower->id,
                $date,
                $achievementValue,
                null,
                null
            );
        }
    }
}
