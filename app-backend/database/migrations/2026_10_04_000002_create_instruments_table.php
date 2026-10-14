<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('instrument_code', 50)->unique();
            $table->string('name', 150);
            $table->string('brand_model', 150)->nullable();
            $table->string('measuring_range', 150)->nullable();
            $table->date('calibration_due_date');
            $table->date('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruments');
    }
};
