<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Proposal Logbook Digital IQC (12 Entities ERD).
     */
    public function up(): void
    {
        // 1. Ensure users table has status_aktif & nip alias if needed
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'status_aktif')) {
                    $table->boolean('status_aktif')->default(true)->after('role');
                }
                if (!Schema::hasColumn('users', 'nip')) {
                    $table->string('nip')->nullable()->after('nik');
                }
            });
        }

        // 2. Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_supplier')->unique();
            $table->string('nama_supplier');
            $table->text('alamat')->nullable();
            $table->string('kontak')->nullable();
            $table->string('negara_asal')->default('Indonesia');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 3. Parts (Proposal spec: kode_part, nama_part, spesifikasi, kategori_komoditas, standar_aql, level_inspeksi)
        // If old parts table exists, drop it to match proposal schema
        Schema::dropIfExists('inspection_measurements');
        Schema::dropIfExists('inspection_photos');
        Schema::dropIfExists('defects');
        Schema::dropIfExists('part_inspection_parameters');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('incoming_materials');
        Schema::dropIfExists('parts');

        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('kode_part')->unique();
            $table->string('nama_part');
            $table->text('spesifikasi')->nullable();
            // 3 Commodities: ee_part, automotive, wire_harness
            $table->enum('kategori_komoditas', ['ee_part', 'automotive', 'wire_harness'])->default('ee_part');
            $table->string('standar_aql')->default('AQL 0.65 / 1.0 (MIL-STD-105E)');
            $table->string('level_inspeksi')->default('General Inspection Level II');
            $table->timestamps();
        });

        // 4. Part Parameters
        Schema::create('part_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->string('nama_parameter');
            $table->decimal('nominal', 12, 4)->nullable();
            $table->decimal('batas_min', 12, 4)->nullable();
            $table->decimal('batas_max', 12, 4)->nullable();
            $table->string('satuan')->default('mm'); // mm, V, Ohm, uF, Visual
            $table->timestamps();
        });

        // 5. Defect Categories
        Schema::create('defect_categories', function (Blueprint $table) {
            $table->id();
            $table->string('kode_defect')->unique();
            $table->string('nama_defect');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 6. Receivings (Material Arrivals / GRN & Queue)
        Schema::create('receivings', function (Blueprint $table) {
            $table->id();
            $table->string('no_grn_iqa')->unique();
            $table->dateTime('tanggal_terima');
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->string('no_po');
            $table->string('no_lot');
            $table->integer('qty_datang');
            $table->enum('status_antrean', ['menunggu_inspeksi', 'dalam_inspeksi', 'selesai'])->default('menunggu_inspeksi');
            $table->timestamps();
        });

        // 7. Logbooks
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_id')->constrained('receivings')->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->integer('qty_sampel')->default(5);
            $table->enum('status', [
                'draft',
                'menunggu_penetapan',
                'perlu_revisi',
                'accept',
                'return_to_vendor',
                'scrap',
                'rework'
            ])->default('draft');
            $table->dateTime('tanggal_inspeksi');
            $table->timestamps();
        });

        // 8. Measurements
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_id')->constrained('logbooks')->cascadeOnDelete();
            $table->foreignId('part_parameter_id')->constrained('part_parameters')->cascadeOnDelete();
            $table->integer('sample_number'); // 1, 2, 3, 4, 5...
            $table->decimal('nilai_ukur', 12, 4)->nullable();
            $table->enum('status_toleransi', ['pass', 'fail'])->default('pass');
            $table->timestamps();
        });

        // 9. Defects
        Schema::create('defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_id')->constrained('logbooks')->cascadeOnDelete();
            $table->foreignId('defect_category_id')->constrained('defect_categories')->cascadeOnDelete();
            $table->integer('qty_defect')->default(1);
            $table->enum('tingkat_keparahan', ['minor', 'major', 'critical'])->default('major');
            $table->text('deskripsi')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->timestamps();
        });

        // 10. NEST Processes (Not Evaluated Special Track)
        Schema::create('nest_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_id')->constrained('logbooks')->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->text('kronologi_masalah');
            $table->string('bukti_pendukung')->nullable();
            $table->dateTime('diajukan_pada');
            $table->timestamps();
        });

        // 11. MRB Dispositions (Material Review Board)
        Schema::create('mrb_dispositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_id')->constrained('logbooks')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status_keputusan', ['accept', 'return_to_vendor', 'scrap', 'rework', 'perlu_revisi']);
            $table->text('catatan');
            $table->dateTime('diputuskan_pada');
            $table->timestamps();
        });

        // 12. Audit Trails (Immutable system log)
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi'); // e.g. LOGIN, POKA_YOKE_REJECTED, CREATE_GRN, SUBMIT_INSPECTION, NEST_INITIATED, MRB_DISPOSITION, USER_CREATED, etc.
            $table->string('objek_data'); // e.g. Lot #..., Receiving #...
            $table->text('nilai_sebelum')->nullable();
            $table->text('nilai_sesudah')->nullable();
            $table->string('alamat_ip')->nullable();
            $table->dateTime('waktu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
        Schema::dropIfExists('mrb_dispositions');
        Schema::dropIfExists('nest_processes');
        Schema::dropIfExists('defects');
        Schema::dropIfExists('measurements');
        Schema::dropIfExists('logbooks');
        Schema::dropIfExists('receivings');
        Schema::dropIfExists('defect_categories');
        Schema::dropIfExists('part_parameters');
        Schema::dropIfExists('parts');
        Schema::dropIfExists('suppliers');
    }
};

