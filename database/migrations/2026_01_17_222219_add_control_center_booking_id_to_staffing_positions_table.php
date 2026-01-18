<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staffing_positions', function (Blueprint $table) {
            $table->unsignedBigInteger('control_center_booking_id')->nullable()->after('discord_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staffing_positions', function (Blueprint $table) {
            $table->dropColumn('control_center_booking_id');
        });
    }
};
