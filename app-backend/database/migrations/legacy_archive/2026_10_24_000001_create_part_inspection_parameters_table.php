<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_inspection_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->onDelete('cascade');
            $table->string('parameter_name');
            $table->foreignId('instrument_id')->nullable()->constrained('instruments')->nullOnDelete();
            $table->decimal('nominal_dimension', 8, 3);
            $table->decimal('tolerance_min', 8, 3);
            $table->decimal('tolerance_max', 8, 3);
            $table->string('unit', 20)->default('mm');
            $table->boolean('is_critical')->default(true);
            $table->timestamps();
        });

        Schema::table('inspection_measurements', function (Blueprint $table) {
            $table->string('parameter_name')->nullable()->after('sample_number');
            $table->string('measurement_tool')->nullable()->after('parameter_name');
            $table->decimal('nominal_value', 8, 3)->nullable()->after('actual_value');
            $table->decimal('tolerance_min', 8, 3)->nullable()->after('nominal_value');
            $table->decimal('tolerance_max', 8, 3)->nullable()->after('tolerance_min');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_measurements', function (Blueprint $table) {
            $table->dropColumn(['parameter_name', 'measurement_tool', 'nominal_value', 'tolerance_min', 'tolerance_max']);
        });

        Schema::dropIfExists('part_inspection_parameters');
    }
};
