<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('semester_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->nullable()->constrained();
            $table->string('name');
            $table->date('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_schedules');
    }
};
