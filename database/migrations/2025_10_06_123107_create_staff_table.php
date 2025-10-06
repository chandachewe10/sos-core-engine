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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('address')->nullable();
            $table->string('password');
            $table->string('hpcz_number')->nullable();
            $table->string('nrc_uri')->nullable();
            $table->string('selfie_uri')->nullable();
            $table->string('signature_uri')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
