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
        Schema::create('data_submission_details', function (Blueprint $t) {
            $t->id();
            $t->foreignId('data_submission_id')->constrained('data_submissions')->cascadeOnDelete();
            $t->foreignId('job_category_id')->constrained('job_categories')->cascadeOnDelete();
            $t->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $t->decimal('volume', 16, 2)->nullable();
            $t->foreignId('satuan_id')->nullable()->constrained('units')->nullOnDelete();
            $t->text('keterangan')->nullable();
            $t->timestamps();

            $t->unique(['data_submission_id', 'item_id']); // item tidak dobel dalam satu submit
            $t->index(['job_category_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_submission_details');
    }
};
