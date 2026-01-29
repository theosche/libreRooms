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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('contacts')->cascadeOnDelete();

            $table->string('hash')->unique();
            $table->string('status');
            $table->string('title');
            $table->text('description')->nullable();

            $table->integer('full_price');
            $table->decimal('sum_discounts', 10, 2);
            $table->decimal('special_discount', 10, 2)->nullable();
            $table->decimal('donation', 10, 2)->nullable();

            $table->text('custom_message')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
