<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->unique();
            $table->string('part_name');
            $table->string('supplier_name');
            $table->string('sampling_level')->default('General II');
            $table->string('aql_level')->default('1.0');
            $table->decimal('nominal_dimension', 8, 3);
            $table->decimal('tolerance_min', 8, 3);
            $table->decimal('tolerance_max', 8, 3);
            $table->string('unit')->default('mm');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
