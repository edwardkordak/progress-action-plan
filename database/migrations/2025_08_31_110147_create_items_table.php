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
        Schema::create('items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $t->foreignId('job_category_id')->constrained('job_categories')->cascadeOnDelete();
            $t->string('name'); // nama item pekerjaan
            $t->bigInteger('price'); // harga item pekerjaan
            $t->foreignId('default_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $t->timestamps();
            $t->unique(['package_id', 'job_category_id', 'name']); // unik dalam paket+jenis
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
