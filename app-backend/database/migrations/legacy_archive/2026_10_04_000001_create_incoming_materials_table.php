<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_materials', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number', 50)->unique();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->string('lot_number', 50)->nullable();
            $table->unsignedInteger('quantity');
            $table->dateTime('arrived_at');
            // waiting = in IQC queue, inspected = inspection submitted
            $table->string('status', 20)->default('waiting');
            $table->foreignId('inspection_id')->nullable()->constrained('inspections')->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_materials');
    }
};
