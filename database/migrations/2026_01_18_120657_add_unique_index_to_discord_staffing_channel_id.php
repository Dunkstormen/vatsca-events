<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clean up any duplicate discord_staffing_channel_id values
        // Keep the first event with each channel_id, set others to NULL
        $duplicates = DB::table('events')
            ->select('discord_staffing_channel_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('discord_staffing_channel_id')
            ->groupBy('discord_staffing_channel_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all events with this channel ID
            $events = DB::table('events')
                ->where('discord_staffing_channel_id', $duplicate->discord_staffing_channel_id)
                ->orderBy('id')
                ->get();
            
            // Keep the first one, clear the rest
            $first = true;
            foreach ($events as $event) {
                if ($first) {
                    $first = false;
                    continue;
                }
                DB::table('events')
                    ->where('id', $event->id)
                    ->update(['discord_staffing_channel_id' => null]);
            }
        }

        Schema::table('events', function (Blueprint $table) {
            // Add unique index to discord_staffing_channel_id
            // This ensures no two events can use the same Discord channel
            $table->unique('discord_staffing_channel_id', 'events_discord_channel_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique('events_discord_channel_unique');
        });
    }
};
