<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Part;
use App\Models\PartParameter;
use App\Models\DefectCategory;
use App\Models\Receiving;
use App\Models\Logbook;
use App\Models\Measurement;
use App\Models\Defect;
use App\Models\NestProcess;
use App\Models\MrbDisposition;
use App\Models\AuditTrail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------
        // 1. USERS & 6 ROLES (Proposal Tabel 3.5 & UC-01)
        // ----------------------------------------------------
        $admin = User::updateOrCreate(
            ['nik' => 'ADM-001'],
            [
                'name' => 'Aditya Ryan Wardana',
                'nip' => 'ADM-001',
                'role' => 'admin',
                'status_aktif' => true,
                'shift' => '1',
                'email' => 'admin@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        $logistik = User::updateOrCreate(
            ['nik' => 'LOG-001'],
            [
                'name' => 'Bambang Sudarsono',
                'nip' => 'LOG-001',
                'role' => 'master_data',
                'status_aktif' => true,
                'shift' => '1',
                'email' => 'logistik@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        $supervisor = User::updateOrCreate(
            ['nik' => 'SPV-001'],
            [
                'name' => 'Kislistiyono',
                'nip' => 'SPV-001',
                'role' => 'supervisor',
                'status_aktif' => true,
                'shift' => '1',
                'email' => 'supervisor@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        $inspectorEE = User::updateOrCreate(
            ['nik' => 'INS-EE-001'],
            [
                'name' => 'Serlin Yolanda',
                'nip' => '3312411105',
                'role' => 'inspector_ee',
                'status_aktif' => true,
                'shift' => '1',
                'email' => 'serlin.yolanda@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        $inspectorAuto = User::updateOrCreate(
            ['nik' => 'INS-AUTO-001'],
            [
                'name' => 'Rudi Hartono',
                'nip' => '3312411106',
                'role' => 'inspector_automotive',
                'status_aktif' => true,
                'shift' => '2',
                'email' => 'rudi.hartono@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        $inspectorWH = User::updateOrCreate(
            ['nik' => 'INS-WH-001'],
            [
                'name' => 'Ahmad Fauzi',
                'nip' => '3312411107',
                'role' => 'inspector_wire_harness',
                'status_aktif' => true,
                'shift' => '1',
                'email' => 'ahmad.fauzi@iqc.local',
                'password' => Hash::make('password123'),
            ]
        );

        // ----------------------------------------------------
        // 2. SUPPLIERS (UC-13)
        // ----------------------------------------------------
        $suppliers = [
            'STM' => Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-STM-01'],
                [
                    'nama_supplier' => 'PT STMicroelectronics Indonesia',
                    'alamat' => 'Jl. Beringin Mukakuning, Batamindo Industrial Park, Batam',
                    'kontak' => '+62 778 611200',
                    'negara_asal' => 'Indonesia',
                    'status_aktif' => true,
                ]
            ),
            'TDK' => Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-TDK-02'],
                [
                    'nama_supplier' => 'PT TDK Electronics Indonesia',
                    'alamat' => 'Kawasan Industri Panbil Lot 1-4, Mukakuning, Batam',
                    'kontak' => '+62 778 404100',
                    'negara_asal' => 'Indonesia',
                    'status_aktif' => true,
                ]
            ),
            'OMR' => Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-OMR-03'],
                [
                    'nama_supplier' => 'PT Omron Manufacturing Indonesia',
                    'alamat' => 'EJIP Industrial Park Plot 5C, Cikarang Selatan, Bekasi',
                    'kontak' => '+62 21 8970111',
                    'negara_asal' => 'Indonesia',
                    'status_aktif' => true,
                ]
            ),
            'TXI' => Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-TXI-04'],
                [
                    'nama_supplier' => 'PT Texas Instruments Batam',
                    'alamat' => 'Batamindo Industrial Park Blok 12, Batam',
                    'kontak' => '+62 778 611500',
                    'negara_asal' => 'Indonesia',
                    'status_aktif' => true,
                ]
            ),
            'HRS' => Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-HRS-05'],
                [
                    'nama_supplier' => 'PT Hirose Electric Indonesia',
                    'alamat' => 'EJIP Industrial Park Plot 3B-1, Cikarang, Jawa Barat',
                    'kontak' => '+62 21 8970222',
                    'negara_asal' => 'Indonesia',
                    'status_aktif' => true,
                ]
            ),
            'YAZ' => Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-YAZ-06'],
                [
                    'nama_supplier' => 'PT Yazaki Wiring Technologies',
                    'alamat' => 'Kota Bukit Indah Industrial Estate, Purwakarta',
                    'kontak' => '+62 264 351888',
                    'negara_asal' => 'Indonesia',
                    'status_aktif' => true,
                ]
            ),
            'DEN' => Supplier::updateOrCreate(
                ['kode_supplier' => 'SUP-DEN-07'],
                [
                    'nama_supplier' => 'PT Denso Indonesia',
                    'alamat' => 'Kawasan Industri MM2100 Blok KK, Cikarang Barat',
                    'kontak' => '+62 21 8980333',
                    'negara_asal' => 'Indonesia',
                    'status_aktif' => true,
                ]
            ),
        ];

        // ----------------------------------------------------
        // 3. DEFECT CATEGORIES (UC-13 & Pareto Chart UC-11)
        // ----------------------------------------------------
        $defCategories = [
            'DEF-PIN-01' => DefectCategory::updateOrCreate(
                ['kode_defect' => 'DEF-PIN-01'],
                ['nama_defect' => 'Solder Bridge / Pin Bent', 'deskripsi' => 'Pin bengkok, patah, atau terdapat short solder bridge antar pin IC.']
            ),
            'DEF-PKG-02' => DefectCategory::updateOrCreate(
                ['kode_defect' => 'DEF-PKG-02'],
                ['nama_defect' => 'Damaged Packaging / Seal Void', 'deskripsi' => 'Kemasan reel atau moisture barrier bag sobek atau HIC (Humidity Indicator) berubah warna.']
            ),
            'DEF-OX-03' => DefectCategory::updateOrCreate(
                ['kode_defect' => 'DEF-OX-03'],
                ['nama_defect' => 'Lead Pin Oxidation / Tarnish', 'deskripsi' => 'Oksidasi pada terminal atau kaki komponen yang mengganggu solderability.']
            ),
            'DEF-CRK-04' => DefectCategory::updateOrCreate(
                ['kode_defect' => 'DEF-CRK-04'],
                ['nama_defect' => 'Ceramic Body Crack / Chipping', 'deskripsi' => 'Retakan mikro atau gumpil pada body keramik kapasitor atau IC mold package.']
            ),
            'DEF-POL-05' => DefectCategory::updateOrCreate(
                ['kode_defect' => 'DEF-POL-05'],
                ['nama_defect' => 'Reverse Polarity / Marking Erased', 'deskripsi' => 'Orientasi marking dot pin 1 terbalik atau kode part pudar/terhapus.']
            ),
            'DEF-INS-06' => DefectCategory::updateOrCreate(
                ['kode_defect' => 'DEF-INS-06'],
                ['nama_defect' => 'Terminal Crimping Loose / Pull-out', 'deskripsi' => 'Crimping terminal wire harness longgar atau tidak tahan uji tarik.']
            ),
        ];

        // ----------------------------------------------------
        // 4. PARTS & PARAMETERS (3 Commodities) (UC-13)
        // ----------------------------------------------------
        // 4.1 EE Part
        $partMcu = Part::updateOrCreate(
            ['kode_part' => 'EE-MCU-3204-STM'],
            [
                'nama_part' => 'Microcontroller 32-Bit ARM Cortex-M4',
                'spesifikasi' => 'LQFP-64 Package, 168MHz, 512KB Flash, RoHS Compliant',
                'kategori_komoditas' => 'ee_part',
                'standar_aql' => 'MIL-STD-105E AQL 0.65',
                'level_inspeksi' => 'General Level II',
            ]
        );
        PartParameter::updateOrCreate(['part_id' => $partMcu->id, 'nama_parameter' => 'Panjang Body (mm)'], ['nominal' => 14.00, 'batas_min' => 13.90, 'batas_max' => 14.10, 'satuan' => 'mm']);
        PartParameter::updateOrCreate(['part_id' => $partMcu->id, 'nama_parameter' => 'Lebar Body (mm)'], ['nominal' => 14.00, 'batas_min' => 13.90, 'batas_max' => 14.10, 'satuan' => 'mm']);
        PartParameter::updateOrCreate(['part_id' => $partMcu->id, 'nama_parameter' => 'Pin Pitch (mm)'], ['nominal' => 0.50, 'batas_min' => 0.45, 'batas_max' => 0.55, 'satuan' => 'mm']);

        $partCap = Part::updateOrCreate(
            ['kode_part' => 'CAP-CER-0805-10U'],
            [
                'nama_part' => 'SMD Ceramic Chip Capacitor 10µF 25V',
                'spesifikasi' => 'Package 0805 X7R ±10%, Tape & Reel Packaging',
                'kategori_komoditas' => 'ee_part',
                'standar_aql' => 'MIL-STD-105E AQL 1.0',
                'level_inspeksi' => 'General Level II',
            ]
        );
        PartParameter::updateOrCreate(['part_id' => $partCap->id, 'nama_parameter' => 'Panjang (mm)'], ['nominal' => 2.00, 'batas_min' => 1.90, 'batas_max' => 2.10, 'satuan' => 'mm']);
        PartParameter::updateOrCreate(['part_id' => $partCap->id, 'nama_parameter' => 'Lebar (mm)'], ['nominal' => 1.25, 'batas_min' => 1.15, 'batas_max' => 1.35, 'satuan' => 'mm']);
        PartParameter::updateOrCreate(['part_id' => $partCap->id, 'nama_parameter' => 'Kapasitansi (µF)'], ['nominal' => 10.00, 'batas_min' => 9.00, 'batas_max' => 11.00, 'satuan' => 'µF']);

        $partIcReg = Part::updateOrCreate(
            ['kode_part' => 'IC-REG-LM2596-5V'],
            [
                'nama_part' => 'Step-Down Voltage Regulator IC 5.0V 3A',
                'spesifikasi' => 'TO-263-5 Surface Mount, Fixed 5.0V Output',
                'kategori_komoditas' => 'ee_part',
                'standar_aql' => 'MIL-STD-105E AQL 0.65',
                'level_inspeksi' => 'General Level II',
            ]
        );
        PartParameter::updateOrCreate(['part_id' => $partIcReg->id, 'nama_parameter' => 'Output Voltage (V)'], ['nominal' => 5.00, 'batas_min' => 4.85, 'batas_max' => 5.15, 'satuan' => 'V']);
        PartParameter::updateOrCreate(['part_id' => $partIcReg->id, 'nama_parameter' => 'Quiescent Current (mA)'], ['nominal' => 5.00, 'batas_min' => 4.00, 'batas_max' => 6.00, 'satuan' => 'mA']);

        // 4.2 Automotive Commodity
        $partAutoRelay = Part::updateOrCreate(
            ['kode_part' => 'AUTO-RLY-12V-40A'],
            [
                'nama_part' => 'Automotive Power Relay 12V 40A Sealed',
                'spesifikasi' => 'ISO Micro 280 Terminal, IP67 Dust/Water Resistant',
                'kategori_komoditas' => 'automotive',
                'standar_aql' => 'MIL-STD-105E AQL 0.40',
                'level_inspeksi' => 'General Level II',
            ]
        );
        PartParameter::updateOrCreate(['part_id' => $partAutoRelay->id, 'nama_parameter' => 'Coil Resistance (Ω)'], ['nominal' => 85.00, 'batas_min' => 80.00, 'batas_max' => 90.00, 'satuan' => 'Ω']);
        PartParameter::updateOrCreate(['part_id' => $partAutoRelay->id, 'nama_parameter' => 'Pull-in Voltage (V)'], ['nominal' => 7.50, 'batas_min' => 7.00, 'batas_max' => 8.00, 'satuan' => 'V']);
        PartParameter::updateOrCreate(['part_id' => $partAutoRelay->id, 'nama_parameter' => 'Terminal Width (mm)'], ['nominal' => 6.30, 'batas_min' => 6.20, 'batas_max' => 6.40, 'satuan' => 'mm']);

        $partAutoSpeed = Part::updateOrCreate(
            ['kode_part' => 'AUTO-SNS-SPD-01'],
            [
                'nama_part' => 'Wheel Speed Sensor Magnetic Pickup',
                'spesifikasi' => 'Hall Effect Wheel Speed Sensor, Operating -40C to 125C',
                'kategori_komoditas' => 'automotive',
                'standar_aql' => 'MIL-STD-105E AQL 0.40',
                'level_inspeksi' => 'General Level II',
            ]
        );
        PartParameter::updateOrCreate(['part_id' => $partAutoSpeed->id, 'nama_parameter' => 'Internal Resistance (Ω)'], ['nominal' => 1200.00, 'batas_min' => 1100.00, 'batas_max' => 1300.00, 'satuan' => 'Ω']);

        // 4.3 Wire Harness Commodity
        $partWhConn = Part::updateOrCreate(
            ['kode_part' => 'WH-CONN-HDR-20P'],
            [
                'nama_part' => 'Wire Harness Header Connector 20-Pin 2.54mm',
                'spesifikasi' => 'Dual Row Shrouded Header with Positive Locking Mechanism',
                'kategori_komoditas' => 'wire_harness',
                'standar_aql' => 'MIL-STD-105E AQL 1.0',
                'level_inspeksi' => 'General Level II',
            ]
        );
        PartParameter::updateOrCreate(['part_id' => $partWhConn->id, 'nama_parameter' => 'Pin Pitch (mm)'], ['nominal' => 2.54, 'batas_min' => 2.49, 'batas_max' => 2.59, 'satuan' => 'mm']);
        PartParameter::updateOrCreate(['part_id' => $partWhConn->id, 'nama_parameter' => 'Terminal Insertion Force (N)'], ['nominal' => 25.00, 'batas_min' => 20.00, 'batas_max' => 30.00, 'satuan' => 'N']);

        $partWhCable = Part::updateOrCreate(
            ['kode_part' => 'WH-CBL-UL1007-22'],
            [
                'nama_part' => 'Hook-Up Lead Wire UL1007 AWG22 PVC Stranded',
                'spesifikasi' => '300V 80C tinned copper wire, 1000ft reel',
                'kategori_komoditas' => 'wire_harness',
                'standar_aql' => 'MIL-STD-105E AQL 1.0',
                'level_inspeksi' => 'General Level II',
            ]
        );
        PartParameter::updateOrCreate(['part_id' => $partWhCable->id, 'nama_parameter' => 'Outer Diameter (mm)'], ['nominal' => 1.60, 'batas_min' => 1.50, 'batas_max' => 1.70, 'satuan' => 'mm']);

        // ----------------------------------------------------
        // 5. RECEIVINGS & QUEUE (UC-02)
        // ----------------------------------------------------
        // Inbound 1: Normal (<12h aging) - EE Part
        $rcv1 = Receiving::updateOrCreate(
            ['no_grn_iqa' => 'GRN-202611-001'],
            [
                'tanggal_terima' => now()->subHours(4),
                'supplier_id' => $suppliers['STM']->id,
                'part_id' => $partMcu->id,
                'no_po' => 'PO-2026-EE-0891',
                'no_lot' => 'LOT-STM-2610A',
                'qty_datang' => 2500,
                'status_antrean' => 'menunggu_inspeksi',
            ]
        );

        // Inbound 2: Prioritas (12 - 24h aging) - EE Part
        $rcv2 = Receiving::updateOrCreate(
            ['no_grn_iqa' => 'GRN-202611-002'],
            [
                'tanggal_terima' => now()->subHours(16),
                'supplier_id' => $suppliers['TDK']->id,
                'part_id' => $partCap->id,
                'no_po' => 'PO-2026-EE-0892',
                'no_lot' => 'LOT-TDK-9901X',
                'qty_datang' => 12000,
                'status_antrean' => 'menunggu_inspeksi',
            ]
        );

        // Inbound 3: Overdue (> 24h aging) - Automotive
        $rcv3 = Receiving::updateOrCreate(
            ['no_grn_iqa' => 'GRN-202611-003'],
            [
                'tanggal_terima' => now()->subHours(32),
                'supplier_id' => $suppliers['DEN']->id,
                'part_id' => $partAutoRelay->id,
                'no_po' => 'PO-2026-AUTO-0112',
                'no_lot' => 'LOT-DEN-8812B',
                'qty_datang' => 3000,
                'status_antrean' => 'menunggu_inspeksi',
            ]
        );

        // Inbound 4: Wire Harness - Dalam Inspeksi
        $rcv4 = Receiving::updateOrCreate(
            ['no_grn_iqa' => 'GRN-202611-004'],
            [
                'tanggal_terima' => now()->subHours(8),
                'supplier_id' => $suppliers['HRS']->id,
                'part_id' => $partWhConn->id,
                'no_po' => 'PO-2026-WH-0433',
                'no_lot' => 'LOT-HRS-8810A',
                'qty_datang' => 5000,
                'status_antrean' => 'dalam_inspeksi',
            ]
        );

        // Inbound 5: Selesai - EE Part
        $rcv5 = Receiving::updateOrCreate(
            ['no_grn_iqa' => 'GRN-202611-005'],
            [
                'tanggal_terima' => now()->subDays(2),
                'supplier_id' => $suppliers['TXI']->id,
                'part_id' => $partIcReg->id,
                'no_po' => 'PO-2026-EE-0870',
                'no_lot' => 'LOT-TXI-4480D',
                'qty_datang' => 3200,
                'status_antrean' => 'selesai',
            ]
        );

        // Inbound 6: Selesai (NEST & MRB Case)
        $rcv6 = Receiving::updateOrCreate(
            ['no_grn_iqa' => 'GRN-202611-006'],
            [
                'tanggal_terima' => now()->subDays(3),
                'supplier_id' => $suppliers['OMR']->id,
                'part_id' => $partMcu->id,
                'no_po' => 'PO-2026-EE-0865',
                'no_lot' => 'LOT-OMR-7711C',
                'qty_datang' => 1500,
                'status_antrean' => 'dalam_inspeksi',
            ]
        );

        // ----------------------------------------------------
        // 6. LOGBOOKS, MEASUREMENTS, DEFECTS, NEST & MRB (UC-04 to UC-10)
        // ----------------------------------------------------
        // 6.1 Logbook ACCEPT (Completed inspection)
        $logbookAccept = Logbook::updateOrCreate(
            ['receiving_id' => $rcv5->id],
            [
                'inspector_id' => $inspectorEE->id,
                'qty_sampel' => 5,
                'status' => 'accept',
                'tanggal_inspeksi' => now()->subDays(2),
            ]
        );
        foreach ($partIcReg->parameters as $param) {
            for ($s = 1; $s <= 5; $s++) {
                $val = $param->nominal + (rand(-10, 10) / 100);
                Measurement::updateOrCreate(
                    ['logbook_id' => $logbookAccept->id, 'part_parameter_id' => $param->id, 'sample_number' => $s],
                    ['nilai_ukur' => $val, 'status_toleransi' => 'pass']
                );
            }
        }
        MrbDisposition::updateOrCreate(
            ['logbook_id' => $logbookAccept->id],
            [
                'supervisor_id' => $supervisor->id,
                'status_keputusan' => 'accept',
                'catatan' => 'Semua parameter dimensi dan output voltage dalam rentang toleransi standar. Lolos QC.',
                'diputuskan_pada' => now()->subDays(2)->addHours(2),
            ]
        );

        // 6.2 Logbook MENUNGGU PENETAPAN MRB (Has Defect & Initiated NEST Process!)
        $logbookNest = Logbook::updateOrCreate(
            ['receiving_id' => $rcv6->id],
            [
                'inspector_id' => $inspectorEE->id,
                'qty_sampel' => 5,
                'status' => 'menunggu_penetapan',
                'tanggal_inspeksi' => now()->subHours(5),
            ]
        );
        foreach ($partMcu->parameters as $param) {
            for ($s = 1; $s <= 5; $s++) {
                $val = $s === 3 ? 14.35 : $param->nominal; // sample 3 out of spec!
                $tol = ($val >= $param->batas_min && $val <= $param->batas_max) ? 'pass' : 'fail';
                Measurement::updateOrCreate(
                    ['logbook_id' => $logbookNest->id, 'part_parameter_id' => $param->id, 'sample_number' => $s],
                    ['nilai_ukur' => $val, 'status_toleransi' => $tol]
                );
            }
        }
        // Defect record
        Defect::updateOrCreate(
            ['logbook_id' => $logbookNest->id, 'defect_category_id' => $defCategories['DEF-PIN-01']->id],
            [
                'qty_defect' => 4,
                'tingkat_keparahan' => 'critical',
                'deskripsi' => 'Ditemukan pin 12 dan 13 bengkok parah (bent leads) pada 4 chip dalam reel 1.',
                'foto_bukti' => 'defects/sample_bent_pin.jpg',
            ]
        );
        // NEST Process
        NestProcess::updateOrCreate(
            ['logbook_id' => $logbookNest->id],
            [
                'inspector_id' => $inspectorEE->id,
                'kronologi_masalah' => 'Pada saat inspeksi visual dan pengukuran sample #3, ditemukan anomali dimensi body dan pin pitch melengkung. Lot berpotensi menyebabkan solder bridging pada lini SMT jika langsung diproses.',
                'bukti_pendukung' => 'nest/nest_evidence_001.pdf',
                'diajukan_pada' => now()->subHours(4),
            ]
        );

        // 6.3 Logbook PERLU REVISI (Supervisor sent back to inspector for re-measurement)
        $rcvRev = Receiving::updateOrCreate(
            ['no_grn_iqa' => 'GRN-202611-007'],
            [
                'tanggal_terima' => now()->subDay(),
                'supplier_id' => $suppliers['HRS']->id,
                'part_id' => $partWhConn->id,
                'no_po' => 'PO-2026-WH-0439',
                'no_lot' => 'LOT-HRS-8899Z',
                'qty_datang' => 2000,
                'status_antrean' => 'dalam_inspeksi',
            ]
        );
        $logbookRev = Logbook::updateOrCreate(
            ['receiving_id' => $rcvRev->id],
            [
                'inspector_id' => $inspectorWH->id,
                'qty_sampel' => 5,
                'status' => 'perlu_revisi',
                'tanggal_inspeksi' => now()->subHours(10),
            ]
        );
        MrbDisposition::updateOrCreate(
            ['logbook_id' => $logbookRev->id],
            [
                'supervisor_id' => $supervisor->id,
                'status_keputusan' => 'perlu_revisi',
                'catatan' => 'Mohon lakukan verifikasi ulang pada alat ukur Caliper No. CAL-04 dan ukur 5 sampel tambahan pada reel ke-2.',
                'diputuskan_pada' => now()->subHours(6),
            ]
        );

        // 6.4 Additional Historical Logbooks for Pareto Analysis & Trends
        $pastCases = [
            ['part' => $partMcu, 'def' => 'DEF-PIN-01', 'qty' => 12, 'sev' => 'major', 'mrb' => 'rework', 'days' => 6],
            ['part' => $partCap, 'def' => 'DEF-CRK-04', 'qty' => 8, 'sev' => 'critical', 'mrb' => 'return_to_vendor', 'days' => 5],
            ['part' => $partAutoRelay, 'def' => 'DEF-PKG-02', 'qty' => 5, 'sev' => 'minor', 'mrb' => 'accept', 'days' => 4],
            ['part' => $partWhConn, 'def' => 'DEF-INS-06', 'qty' => 7, 'sev' => 'major', 'mrb' => 'rework', 'days' => 3],
            ['part' => $partMcu, 'def' => 'DEF-OX-03', 'qty' => 6, 'sev' => 'major', 'mrb' => 'scrap', 'days' => 2],
            ['part' => $partCap, 'def' => 'DEF-POL-05', 'qty' => 3, 'sev' => 'critical', 'mrb' => 'return_to_vendor', 'days' => 1],
        ];

        foreach ($pastCases as $idx => $case) {
            $rcvHist = Receiving::updateOrCreate(
                ['no_grn_iqa' => 'GRN-202610-09' . ($idx + 1)],
                [
                    'tanggal_terima' => now()->subDays($case['days'] + 1),
                    'supplier_id' => $suppliers['STM']->id,
                    'part_id' => $case['part']->id,
                    'no_po' => 'PO-HIST-00' . ($idx + 1),
                    'no_lot' => 'LOT-HIST-90' . ($idx + 1),
                    'qty_datang' => 4000,
                    'status_antrean' => 'selesai',
                ]
            );

            $logHist = Logbook::updateOrCreate(
                ['receiving_id' => $rcvHist->id],
                [
                    'inspector_id' => $inspectorEE->id,
                    'qty_sampel' => 5,
                    'status' => $case['mrb'],
                    'tanggal_inspeksi' => now()->subDays($case['days']),
                ]
            );

            Defect::updateOrCreate(
                ['logbook_id' => $logHist->id, 'defect_category_id' => $defCategories[$case['def']]->id],
                [
                    'qty_defect' => $case['qty'],
                    'tingkat_keparahan' => $case['sev'],
                    'deskripsi' => 'Defect historis untuk analisis Pareto IQC.',
                ]
            );

            MrbDisposition::updateOrCreate(
                ['logbook_id' => $logHist->id],
                [
                    'supervisor_id' => $supervisor->id,
                    'status_keputusan' => $case['mrb'],
                    'catatan' => 'Keputusan disposisi MRB telah disetujui berdasarkan SOP IQC.',
                    'diputuskan_pada' => now()->subDays($case['days'])->addHours(3),
                ]
            );
        }

        // ----------------------------------------------------
        // 7. AUDIT TRAILS (UC-15 & Poka-Yoke Rejection Sample)
        // ----------------------------------------------------
        AuditTrail::create([
            'user_id' => $admin->id,
            'aksi' => 'SYSTEM_INIT',
            'objek_data' => 'Database Schema v5.0 (Proposal 12 Entities)',
            'nilai_sebelum' => null,
            'nilai_sesudah' => 'Database initialized with 12 entities and 6 roles',
            'alamat_ip' => '127.0.0.1',
            'waktu' => now()->subDays(5),
        ]);

        AuditTrail::create([
            'user_id' => $logistik->id,
            'aksi' => 'CREATE_GRN',
            'objek_data' => 'Receiving GRN-202611-001 (Part: EE-MCU-3204-STM)',
            'nilai_sebelum' => null,
            'nilai_sesudah' => 'Lot LOT-STM-2610A Qty 2500 enrolled to incoming queue',
            'alamat_ip' => '192.168.1.45',
            'waktu' => now()->subHours(4),
        ]);

        // Poka-Yoke Rejection logged!
        AuditTrail::create([
            'user_id' => $inspectorEE->id,
            'aksi' => 'POKA_YOKE_REJECTED',
            'objek_data' => 'Receiving GRN-202611-003 (Part: AUTO-RLY-12V-40A, Komoditas: Automotive)',
            'nilai_sebelum' => 'Inspector Role: inspector_ee (Commodity: ee_part)',
            'nilai_sesudah' => 'BLOCKED: Komoditas material (automotive) tidak cocok dengan hak akses inspektur!',
            'alamat_ip' => '192.168.1.88',
            'waktu' => now()->subHours(3),
        ]);

        AuditTrail::create([
            'user_id' => $inspectorEE->id,
            'aksi' => 'NEST_INITIATED',
            'objek_data' => 'Logbook #' . $logbookNest->id . ' (GRN-202611-006)',
            'nilai_sebelum' => 'Status: draft',
            'nilai_sesudah' => 'Status: menunggu_penetapan (Kronologi NEST diajukan ke Supervisor)',
            'alamat_ip' => '192.168.1.88',
            'waktu' => now()->subHours(4),
        ]);

        AuditTrail::create([
            'user_id' => $supervisor->id,
            'aksi' => 'MRB_DISPOSITION',
            'objek_data' => 'Logbook #' . $logbookAccept->id,
            'nilai_sebelum' => 'Status: draft',
            'nilai_sesudah' => 'Status: accept (Disposisi: Accept / Lolos)',
            'alamat_ip' => '192.168.1.12',
            'waktu' => now()->subDays(2),
        ]);

        // ----------------------------------------------------
        // 13. CALIBRATION INSTRUMENTS
        // ----------------------------------------------------
        \App\Models\Instrument::upsert([
            ['instrument_code' => 'INS-CAL-01', 'name' => 'Vernier Caliper Digital', 'brand_model' => 'Mitutoyo 500-196-30', 'measuring_range' => '0 - 150 mm (Resolusi 0.01 mm)', 'calibration_due_date' => '2027-05-15', 'last_verified_at' => now()->toDateString()],
            ['instrument_code' => 'INS-MIC-01', 'name' => 'Digital Outside Micrometer', 'brand_model' => 'Mitutoyo 293-240-30', 'measuring_range' => '0 - 25 mm (Resolusi 0.001 mm)', 'calibration_due_date' => '2027-06-20', 'last_verified_at' => now()->toDateString()],
            ['instrument_code' => 'INS-DMM-01', 'name' => 'Precision Digital Multimeter', 'brand_model' => 'Fluke 87V Industrial', 'measuring_range' => '0 - 1000 V / Resistance / Capacitance', 'calibration_due_date' => '2027-08-10', 'last_verified_at' => now()->toDateString()],
            ['instrument_code' => 'INS-PIN-01', 'name' => 'Pin Gauge Set Grade 1', 'brand_model' => 'EISEN EP Series', 'measuring_range' => '0.200 - 10.000 mm (Toleransi +-0.001 mm)', 'calibration_due_date' => '2027-11-01', 'last_verified_at' => now()->toDateString()],
            ['instrument_code' => 'INS-OPT-01', 'name' => 'High-Accuracy Optical Microscope', 'brand_model' => 'Keyence VHX-7000', 'measuring_range' => 'Perbesaran 20x - 2000x 4K', 'calibration_due_date' => '2027-09-30', 'last_verified_at' => now()->toDateString()],
        ], ['instrument_code'], ['name', 'brand_model', 'measuring_range', 'calibration_due_date', 'last_verified_at']);
    }
}
