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
        Schema::create('data_targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('satker_id')->constrained('satkers')->cascadeOnDelete();
            $t->foreignId('ppk_id')->constrained('ppks')->cascadeOnDelete();
            $t->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $t->date('tanggal');
            $t->timestamps();
            $t->index(['satker_id', 'ppk_id', 'package_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_targets');
    }
};
