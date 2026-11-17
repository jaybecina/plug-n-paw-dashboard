<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supporters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3)->default('USD');
            $table->string('message', 280)->nullable();
            $table->boolean('is_monthly')->default(false);
            $table->dateTime('donated_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supporters');
    }
};
