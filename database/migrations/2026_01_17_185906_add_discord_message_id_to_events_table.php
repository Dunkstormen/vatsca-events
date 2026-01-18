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
        Schema::table('events', function (Blueprint $table) {
            $table->string('discord_staffing_message_id')->nullable()->after('recurrence_rule');
            $table->string('discord_staffing_channel_id')->nullable()->after('discord_staffing_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['discord_staffing_message_id', 'discord_staffing_channel_id']);
        });
    }
};
