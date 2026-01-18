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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('short_description', 255);
            $table->text('long_description');
            $table->string('banner_path')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->text('recurrence_rule')->nullable();
            $table->foreignId('recurrence_parent_id')->nullable()->constrained('events')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
