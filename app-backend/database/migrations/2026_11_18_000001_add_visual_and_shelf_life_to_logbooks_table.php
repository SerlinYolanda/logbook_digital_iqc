<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add Visual Checklist & Shelf-Life Verification to Logbooks.
     */
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->string('visual_packaging')->default('pass')->after('qty_sampel');
            $table->string('visual_esd_moisture')->default('pass')->after('visual_packaging');
            $table->string('visual_body')->default('pass')->after('visual_esd_moisture');
            $table->string('visual_terminal')->default('pass')->after('visual_body');
            $table->text('visual_notes')->nullable()->after('visual_terminal');
            
            $table->string('date_code')->nullable()->after('visual_notes');
            $table->date('manufacturing_date')->nullable()->after('date_code');
            $table->date('expiration_date')->nullable()->after('manufacturing_date');
            $table->boolean('is_expired')->default(false)->after('expiration_date');
            $table->string('shelf_life_status')->default('normal')->after('is_expired');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn([
                'visual_packaging',
                'visual_esd_moisture',
                'visual_body',
                'visual_terminal',
                'visual_notes',
                'date_code',
                'manufacturing_date',
                'expiration_date',
                'is_expired',
                'shelf_life_status',
            ]);
        });
    }
};
