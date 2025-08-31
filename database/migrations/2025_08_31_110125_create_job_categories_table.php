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
        Schema::create('job_categories', function (Blueprint $t) {
            $t->id();
            $t->string('code', 10)->unique();   // contoh: GAL, PEM, FIN
            $t->string('name');                 // contoh: Galian, Pemboran, Finishing
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_categories');
    }
};
