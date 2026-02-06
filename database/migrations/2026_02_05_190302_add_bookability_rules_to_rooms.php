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
        Schema::table('rooms', function (Blueprint $table) {
            $table->json('allowed_weekdays')->nullable()->after('reservation_advance_limit');
            $table->time('day_start_time')->nullable()->after('allowed_weekdays');
            $table->time('day_end_time')->nullable()->after('day_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['allowed_weekdays', 'day_start_time', 'day_end_time']);
        });
    }
};
