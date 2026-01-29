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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            $table->string('mail_host');
            $table->integer('mail_port');
            $table->string('mail');
            $table->text('mail_pass');

            $table->string('dav_url')->nullable();
            $table->string('dav_user')->nullable();
            $table->text('dav_pass')->nullable();

            $table->string('webdav_user')->nullable();
            $table->text('webdav_pass')->nullable();
            $table->string('webdav_endpoint')->nullable();
            $table->string('webdav_save_path')->nullable();

            $table->string('timezone')->default('Europe/Zurich');
            $table->string('currency', 3)->default('CHF');
            $table->string('locale', 5)->default('fr-CH');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
