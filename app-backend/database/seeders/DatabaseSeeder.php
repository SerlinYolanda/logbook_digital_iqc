<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Part;
use App\Models\IncomingMaterial;
use App\Models\Inspection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(
            ['nik' => 'ADM-001'],
            [
                'name' => 'Administrator IQC',
                'role' => 'admin',
                'shift' => '1',
                'email' => 'admin@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        $inspector = User::firstOrCreate(
            ['nik' => 'INS-001'],
            [
                'name' => 'Serlin Yolanda',
                'role' => 'inspector',
                'shift' => '1',
                'email' => 'serlin.yolanda@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        $supervisor = User::firstOrCreate(
            ['nik' => 'SPV-001'],
            [
                'name' => 'Kislistiyono',
                'role' => 'supervisor',
                'shift' => '1',
                'email' => 'kislistiyono@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        // Clean up legacy test parts with invalid names if any
        Part::where('part_name', 'like', '%mechanical%')
            ->orWhere('supplier_name', 'like', '%mmmmm%')
            ->delete();

        // 2. Realistic Master Parts (EE Component Division)
        $partsData = [
            [
                'part_number' => 'EE-MCU-3204-STM',
                'part_name' => 'Microcontroller 32-Bit ARM Cortex-M4',
                'supplier_name' => 'PT STMicroelectronics Indonesia',
                'sampling_level' => 'General II',
                'aql_level' => '0.65',
                'nominal_dimension' => 14.000,
                'tolerance_min' => 13.900,
                'tolerance_max' => 14.100,
                'unit' => 'mm',
            ],
            [
                'part_number' => 'CAP-CER-0805-10U',
                'part_name' => 'SMD Ceramic Chip Capacitor 10µF 25V',
                'supplier_name' => 'PT TDK Electronics Indonesia',
                'sampling_level' => 'General II',
                'aql_level' => '1.0',
                'nominal_dimension' => 2.000,
                'tolerance_min' => 1.900,
                'tolerance_max' => 2.100,
                'unit' => 'mm',
            ],
            [
                'part_number' => 'RLY-OMR-12V-10A',
                'part_name' => 'Mini Power PCB Relay 12VDC 10A SPDT',
                'supplier_name' => 'PT Omron Manufacturing Indonesia',
                'sampling_level' => 'General II',
                'aql_level' => '0.65',
                'nominal_dimension' => 19.500,
                'tolerance_min' => 19.300,
                'tolerance_max' => 19.700,
                'unit' => 'mm',
            ],
            [
                'part_number' => 'IC-REG-LM2596-5V',
                'part_name' => 'Step-Down Voltage Regulator IC 5.0V 3A',
                'supplier_name' => 'PT Texas Instruments Batam',
                'sampling_level' => 'General II',
                'aql_level' => '1.0',
                'nominal_dimension' => 10.160,
                'tolerance_min' => 10.050,
                'tolerance_max' => 10.270,
                'unit' => 'mm',
            ],
            [
                'part_number' => 'CONN-HDR-254-20P',
                'part_name' => 'Dual Row Pin Header 2.54mm Pitch 2x10P',
                'supplier_name' => 'PT Hirose Electric Indonesia',
                'sampling_level' => 'General II',
                'aql_level' => '1.5',
                'nominal_dimension' => 25.400,
                'tolerance_min' => 25.200,
                'tolerance_max' => 25.600,
                'unit' => 'mm',
            ],
            [
                'part_number' => 'DIO-SCH-1N5819',
                'part_name' => 'Schottky Barrier Rectifier Diode 1A 40V',
                'supplier_name' => 'PT Vishay Semiconductor Indonesia',
                'sampling_level' => 'General II',
                'aql_level' => '1.0',
                'nominal_dimension' => 5.200,
                'tolerance_min' => 5.000,
                'tolerance_max' => 5.400,
                'unit' => 'mm',
            ],
            [
                'part_number' => 'WH-ENG-901-HARN',
                'part_name' => 'Engine Main Wiring Harness Assembly (Non-EE)',
                'supplier_name' => 'PT Yazaki Wiring Systems Indonesia',
                'sampling_level' => 'General II',
                'aql_level' => '0.65',
                'nominal_dimension' => 1250.000,
                'tolerance_min' => 1240.000,
                'tolerance_max' => 1260.000,
                'unit' => 'mm',
            ],
            [
                'part_number' => 'AUTO-CHS-703-BRK',
                'part_name' => 'Automotive Chassis Stamping Bracket (Non-EE)',
                'supplier_name' => 'PT Astra Otoparts Tbk',
                'sampling_level' => 'General II',
                'aql_level' => '1.0',
                'nominal_dimension' => 125.000,
                'tolerance_min' => 124.500,
                'tolerance_max' => 125.500,
                'unit' => 'mm',
            ],
        ];

        $createdParts = [];
        foreach ($partsData as $p) {
            $createdParts[$p['part_number']] = Part::updateOrCreate(
                ['part_number' => $p['part_number']],
                $p
            );
        }

        // 3. Realistic Incoming Material Queue
        $queueData = [
            [
                'grn_number' => 'GRN-20261021-001',
                'part_id' => $createdParts['EE-MCU-3204-STM']->id,
                'lot_number' => 'LOT-STM-2610A',
                'quantity' => 2500,
                'arrived_at' => Carbon::now()->subMinutes(45),
                'status' => 'waiting',
                'registered_by' => $inspector->id,
            ],
            [
                'grn_number' => 'GRN-WH-20261025-01',
                'part_id' => $createdParts['WH-ENG-901-HARN']->id,
                'lot_number' => 'LOT-YZK-2610X',
                'quantity' => 450,
                'arrived_at' => Carbon::now()->subMinutes(30),
                'status' => 'waiting',
                'registered_by' => $inspector->id,
            ],
            [
                'grn_number' => 'GRN-AUTO-20261025-01',
                'part_id' => $createdParts['AUTO-CHS-703-BRK']->id,
                'lot_number' => 'LOT-ASTRA-890B',
                'quantity' => 1200,
                'arrived_at' => Carbon::now()->subMinutes(15),
                'status' => 'waiting',
                'registered_by' => $inspector->id,
            ],
            [
                'grn_number' => 'GRN-20261021-002',
                'part_id' => $createdParts['CAP-CER-0805-10U']->id,
                'lot_number' => 'LOT-TDK-9942B',
                'quantity' => 10000,
                'arrived_at' => Carbon::now()->subHours(2),
                'status' => 'waiting',
                'registered_by' => $inspector->id,
            ],
            [
                'grn_number' => 'GRN-20261021-003',
                'part_id' => $createdParts['RLY-OMR-12V-10A']->id,
                'lot_number' => 'LOT-OMR-7711C',
                'quantity' => 1500,
                'arrived_at' => Carbon::now()->subHours(3)->subMinutes(15),
                'status' => 'waiting',
                'registered_by' => $inspector->id,
            ],
            [
                'grn_number' => 'GRN-20261021-004',
                'part_id' => $createdParts['IC-REG-LM2596-5V']->id,
                'lot_number' => 'LOT-TI-4480D',
                'quantity' => 3200,
                'arrived_at' => Carbon::now()->subHours(4),
                'status' => 'waiting',
                'registered_by' => $inspector->id,
            ],
        ];

        foreach ($queueData as $q) {
            IncomingMaterial::updateOrCreate(
                ['grn_number' => $q['grn_number']],
                $q
            );
        }

        // 4. Sample Completed/Recent Inspections for display
        $inspectionData = [
            [
                'po_number' => 'PO-2026-EE-0891',
                'lot_number' => 'LOT-HRS-8810A',
                'part_id' => $createdParts['CONN-HDR-254-20P']->id,
                'inspector_id' => $inspector->id,
                'supervisor_id' => $supervisor->id,
                'lot_quantity' => 5000,
                'sample_size' => 200,
                'status' => 'approved',
                'disposition' => 'Use As Is',
                'disposition_notes' => 'Semua parameter dimensi pin pitch dan solderability sesuai standar MIL-STD-105E AQL 1.5.',
                'inspection_date' => Carbon::today(),
            ],
            [
                'po_number' => 'PO-2026-EE-0885',
                'lot_number' => 'LOT-VSH-5520F',
                'part_id' => $createdParts['DIO-SCH-1N5819']->id,
                'inspector_id' => $inspector->id,
                'supervisor_id' => null,
                'lot_quantity' => 8000,
                'sample_size' => 315,
                'status' => 'pending_approval',
                'disposition' => null,
                'disposition_notes' => 'Pengujian drop tegangan forward voltage Vf normal. Menunggu verifikasi supervisor.',
                'inspection_date' => Carbon::today(),
            ],
            [
                'po_number' => 'PO-2026-EE-0870',
                'lot_number' => 'LOT-TDK-9901X',
                'part_id' => $createdParts['CAP-CER-0805-10U']->id,
                'inspector_id' => $inspector->id,
                'supervisor_id' => $supervisor->id,
                'lot_quantity' => 12000,
                'sample_size' => 315,
                'status' => 'approved',
                'disposition' => 'Use As Is',
                'disposition_notes' => 'Capacitance dan ESR dalam rentang toleransi nominal 10uF +/- 10%. Passed.',
                'inspection_date' => Carbon::yesterday(),
            ],
        ];

        foreach ($inspectionData as $ins) {
            Inspection::firstOrCreate(
                ['po_number' => $ins['po_number']],
                $ins
            );
        }

        // 5. Default Metrology Instruments (Digital Caliper, Micrometer, etc.)
        $instrumentData = [
            [
                'instrument_code' => 'CAL-001',
                'name' => 'Digital Caliper 150mm',
                'brand_model' => 'Mitutoyo 500-196-30 AOS',
                'measuring_range' => '0 - 150 mm (Res: 0.01 mm)',
                'calibration_due_date' => Carbon::now()->addMonths(8),
                'last_verified_at' => Carbon::today(),
            ],
            [
                'instrument_code' => 'MIC-001',
                'name' => 'Digital Outside Micrometer',
                'brand_model' => 'Mitutoyo 293-240-30 IP65',
                'measuring_range' => '0 - 25 mm (Res: 0.001 mm)',
                'calibration_due_date' => Carbon::now()->addMonths(9),
                'last_verified_at' => Carbon::today(),
            ],
            [
                'instrument_code' => 'HG-001',
                'name' => 'Digital Height Gauge',
                'brand_model' => 'Mitutoyo 192-630-10',
                'measuring_range' => '0 - 300 mm (Res: 0.01 mm)',
                'calibration_due_date' => Carbon::now()->addMonths(6),
                'last_verified_at' => Carbon::today(),
            ],
            [
                'instrument_code' => 'PIN-001',
                'name' => 'Precision Pin Gauge Set',
                'brand_model' => 'Eisen EP Series Class 1',
                'measuring_range' => '0.50 - 10.00 mm (Step 0.01 mm)',
                'calibration_due_date' => Carbon::now()->addMonths(11),
                'last_verified_at' => Carbon::today(),
            ],
        ];

        foreach ($instrumentData as $insItem) {
            \App\Models\Instrument::updateOrCreate(
                ['instrument_code' => $insItem['instrument_code']],
                $insItem
            );
        }

        // 6. Dynamic Test Parameters per Part (Specific Parameters & Assigned Instruments)
        $instruments = \App\Models\Instrument::all()->keyBy('instrument_code');
        $allParts = \App\Models\Part::all()->keyBy('part_number');

        $partParametersData = [
            'EE-MCU-3204-STM' => [
                [
                    'parameter_name' => 'Body Thickness / Ketebalan Bodi',
                    'instrument_code' => 'CAL-001',
                    'nominal_dimension' => 14.000,
                    'tolerance_min' => 13.900,
                    'tolerance_max' => 14.100,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Pin Pitch / Jarak Kaki IC',
                    'instrument_code' => 'MIC-001',
                    'nominal_dimension' => 0.500,
                    'tolerance_min' => 0.480,
                    'tolerance_max' => 0.520,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Total Package Height / Tinggi IC',
                    'instrument_code' => 'MIC-001',
                    'nominal_dimension' => 1.450,
                    'tolerance_min' => 1.350,
                    'tolerance_max' => 1.550,
                    'unit' => 'mm',
                    'is_critical' => false,
                ],
            ],
            'CAP-CER-0805-10U' => [
                [
                    'parameter_name' => 'Chip Length / Panjang Chip (L)',
                    'instrument_code' => 'MIC-001',
                    'nominal_dimension' => 2.000,
                    'tolerance_min' => 1.900,
                    'tolerance_max' => 2.100,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Chip Width / Lebar Chip (W)',
                    'instrument_code' => 'MIC-001',
                    'nominal_dimension' => 1.250,
                    'tolerance_min' => 1.150,
                    'tolerance_max' => 1.350,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Termination Thickness / Tebal Elektroda',
                    'instrument_code' => 'MIC-001',
                    'nominal_dimension' => 0.500,
                    'tolerance_min' => 0.400,
                    'tolerance_max' => 0.600,
                    'unit' => 'mm',
                    'is_critical' => false,
                ],
            ],
            'RLY-OMR-12V-10A' => [
                [
                    'parameter_name' => 'Body Length / Panjang Bodi Relay',
                    'instrument_code' => 'CAL-001',
                    'nominal_dimension' => 19.500,
                    'tolerance_min' => 19.300,
                    'tolerance_max' => 19.700,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Case Height / Tinggi Casing',
                    'instrument_code' => 'HG-001',
                    'nominal_dimension' => 15.500,
                    'tolerance_min' => 15.300,
                    'tolerance_max' => 15.700,
                    'unit' => 'mm',
                    'is_critical' => false,
                ],
                [
                    'parameter_name' => 'Terminal Pin Diameter',
                    'instrument_code' => 'PIN-001',
                    'nominal_dimension' => 1.000,
                    'tolerance_min' => 0.950,
                    'tolerance_max' => 1.050,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
            ],
            'CONN-HDR-254-20P' => [
                [
                    'parameter_name' => 'Total Housing Length / Panjang Total',
                    'instrument_code' => 'CAL-001',
                    'nominal_dimension' => 25.400,
                    'tolerance_min' => 25.200,
                    'tolerance_max' => 25.600,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Pin Pitch / Jarak Antar Pin',
                    'instrument_code' => 'PIN-001',
                    'nominal_dimension' => 2.540,
                    'tolerance_min' => 2.500,
                    'tolerance_max' => 2.580,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Pin Extension Height / Tinggi Pin',
                    'instrument_code' => 'HG-001',
                    'nominal_dimension' => 6.000,
                    'tolerance_min' => 5.850,
                    'tolerance_max' => 6.150,
                    'unit' => 'mm',
                    'is_critical' => false,
                ],
            ],
            'DIO-SCH-1N5819' => [
                [
                    'parameter_name' => 'Body Length / Panjang Bodi DO-41',
                    'instrument_code' => 'CAL-001',
                    'nominal_dimension' => 5.200,
                    'tolerance_min' => 5.000,
                    'tolerance_max' => 5.400,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Body Outer Diameter / Diameter Bodi',
                    'instrument_code' => 'MIC-001',
                    'nominal_dimension' => 2.700,
                    'tolerance_min' => 2.600,
                    'tolerance_max' => 2.800,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Lead Wire Diameter / Tebal Kawat',
                    'instrument_code' => 'PIN-001',
                    'nominal_dimension' => 0.800,
                    'tolerance_min' => 0.750,
                    'tolerance_max' => 0.850,
                    'unit' => 'mm',
                    'is_critical' => false,
                ],
            ],
            'IC-REG-LM2596-5V' => [
                [
                    'parameter_name' => 'Tab Width / Lebar Tab Heatsink',
                    'instrument_code' => 'CAL-001',
                    'nominal_dimension' => 10.160,
                    'tolerance_min' => 10.050,
                    'tolerance_max' => 10.270,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Package Thickness / Tebal Casing TO-220',
                    'instrument_code' => 'CAL-001',
                    'nominal_dimension' => 4.500,
                    'tolerance_min' => 4.350,
                    'tolerance_max' => 4.650,
                    'unit' => 'mm',
                    'is_critical' => false,
                ],
                [
                    'parameter_name' => 'Lead Pitch / Jarak Antar Kaki',
                    'instrument_code' => 'PIN-001',
                    'nominal_dimension' => 1.700,
                    'tolerance_min' => 1.600,
                    'tolerance_max' => 1.800,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
            ],
            'CCT-933-168' => [
                [
                    'parameter_name' => 'Flange Outer Diameter / Diameter Luar',
                    'instrument_code' => 'CAL-001',
                    'nominal_dimension' => 23.800,
                    'tolerance_min' => 22.500,
                    'tolerance_max' => 25.100,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Flange Thickness / Tebal Flensa',
                    'instrument_code' => 'MIC-001',
                    'nominal_dimension' => 8.500,
                    'tolerance_min' => 8.350,
                    'tolerance_max' => 8.650,
                    'unit' => 'mm',
                    'is_critical' => false,
                ],
            ],
            'RCT-993-169' => [
                [
                    'parameter_name' => 'Main Casing Width / Lebar Bodi',
                    'instrument_code' => 'CAL-001',
                    'nominal_dimension' => 24.800,
                    'tolerance_min' => 24.500,
                    'tolerance_max' => 25.100,
                    'unit' => 'mm',
                    'is_critical' => true,
                ],
                [
                    'parameter_name' => 'Bore Depth / Kedalaman Lubang',
                    'instrument_code' => 'HG-001',
                    'nominal_dimension' => 12.000,
                    'tolerance_min' => 11.900,
                    'tolerance_max' => 12.100,
                    'unit' => 'mm',
                    'is_critical' => false,
                ],
            ],
        ];

        foreach ($partParametersData as $partNum => $params) {
            if (isset($allParts[$partNum])) {
                $pId = $allParts[$partNum]->id;
                foreach ($params as $param) {
                    $insId = isset($instruments[$param['instrument_code']]) ? $instruments[$param['instrument_code']]->id : null;
                    \App\Models\PartInspectionParameter::updateOrCreate(
                        [
                            'part_id' => $pId,
                            'parameter_name' => $param['parameter_name'],
                        ],
                        [
                            'instrument_id' => $insId,
                            'nominal_dimension' => $param['nominal_dimension'],
                            'tolerance_min' => $param['tolerance_min'],
                            'tolerance_max' => $param['tolerance_max'],
                            'unit' => $param['unit'],
                            'is_critical' => $param['is_critical'],
                        ]
                    );
                }
            }
        }
    }
}
