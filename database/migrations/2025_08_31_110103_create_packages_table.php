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
        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('satker_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ppk_id')->constrained('ppks')->cascadeOnDelete();
            $t->string('nama_paket');
            $t->string('penyedia_jasa');
            $t->string('lokasi')->nullable();   // untuk auto-fill
            $t->timestamps();
            $t->unique(['ppk_id', 'nama_paket']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
