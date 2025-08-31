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
        Schema::create('ppks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('satker_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->timestamps();
            $t->unique(['satker_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppks');
    }
};
