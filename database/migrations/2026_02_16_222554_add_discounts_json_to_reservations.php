<?php

use App\Enums\DiscountTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->json('discounts')->nullable()->after('sum_discounts');
        });

        // Migrate data from reservation_discount pivot table to JSON column
        $pivotRows = DB::table('reservation_discount')
            ->join('room_discounts', 'reservation_discount.room_discount_id', '=', 'room_discounts.id')
            ->join('reservations', 'reservation_discount.reservation_id', '=', 'reservations.id')
            ->select(
                'reservation_discount.reservation_id',
                'room_discounts.id as discount_id',
                'room_discounts.name as discount_name',
                'room_discounts.type as discount_type',
                'room_discounts.value as discount_value',
                'reservations.full_price',
            )
            ->get();

        $grouped = $pivotRows->groupBy('reservation_id');

        foreach ($grouped as $reservationId => $rows) {
            $discountsData = [];
            foreach ($rows as $row) {
                $amount = match ($row->discount_type) {
                    DiscountTypes::FIXED->value => (float) $row->discount_value,
                    DiscountTypes::PERCENT->value => (float) $row->discount_value * (float) $row->full_price / 100,
                    default => 0,
                };
                $discountsData[] = [$row->discount_id, $row->discount_name, round($amount, 2)];
            }
            DB::table('reservations')
                ->where('id', $reservationId)
                ->update(['discounts' => json_encode($discountsData)]);
        }

        Schema::dropIfExists('reservation_discount');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('reservation_discount', function (Blueprint $table) {
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_discount_id')->constrained()->cascadeOnDelete();

            $table->primary(['reservation_id', 'room_discount_id']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('discounts');
        });
    }
};
