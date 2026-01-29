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
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('contact_id')->constrained()->ondelete('cascade');

            $table->string('invoice_due_mode');
            $table->integer('invoice_due_days');
            $table->integer('invoice_due_days_after_reminder');
            $table->integer('max_nb_reminders');
            $table->json('payment_instructions')->nullable();
            $table->string('late_invoices_reminder')->default('never'); // never, daily, weekly, monthly
            $table->date('late_invoices_reminder_sent_at')->nullable();

            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->nullable();
            $table->string('mail')->nullable();
            $table->text('mail_pass')->nullable();

            $table->boolean('use_caldav');
            $table->string('dav_url')->nullable();
            $table->string('dav_user')->nullable();
            $table->text('dav_pass')->nullable();

            $table->boolean('use_webdav');
            $table->string('webdav_user')->nullable();
            $table->text('webdav_pass')->nullable();
            $table->string('webdav_endpoint')->nullable();
            $table->string('webdav_save_path')->nullable();

            $table->string('timezone')->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('locale', 5)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
