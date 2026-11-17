<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_uuid')->unique();
            $table->string('landing_page', 120);
            $table->string('source', 20)->index();
            $table->string('referrer_host')->nullable();
            $table->string('device', 20);
            $table->char('country_code', 2)->index();
            $table->unsignedSmallInteger('page_views');
            $table->unsignedInteger('duration_seconds');
            $table->boolean('bounced');
            $table->dateTime('started_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
