<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vet_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('search_type', 20);
            $table->string('query')->nullable();
            $table->char('country_code', 2);
            $table->unsignedTinyInteger('results_count');
            $table->unsignedInteger('latency_ms');
            $table->string('status', 30)->index();
            $table->dateTime('searched_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_searches');
    }
};
