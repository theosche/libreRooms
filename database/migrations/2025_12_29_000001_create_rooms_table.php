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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_public')->default(true);

            $table->string('price_mode');
            $table->integer('price_short')->nullable();
            $table->integer('price_full_day');
            $table->integer('max_hours_short')->nullable();
            $table->integer('always_short_after')->nullable();
            $table->integer('always_short_before')->nullable();
            $table->integer('allow_late_end_hour')->default(0);

            $table->integer('reservation_cutoff_days')->nullable();
            $table->integer('reservation_advance_limit')->nullable();

            $table->boolean('use_special_discount')->default(false);
            $table->boolean('use_donation')->default(false);

            $table->string('charter_mode');
            $table->text('charter_str')->nullable();
            $table->text('custom_message')->nullable();

            $table->text('secret_message')->nullable();

            $table->string('external_slot_provider')->nullable();
            $table->string('dav_calendar')->nullable();
            $table->string('embed_calendar_mode');
            $table->string('calendar_view_mode');

            $table->string('timezone')->nullable();
            $table->boolean('disable_mailer')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
