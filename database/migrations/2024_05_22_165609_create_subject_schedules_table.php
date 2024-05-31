<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subject_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->unsignedTinyInteger('meeting_no');

            $table->unique(['subject_id', 'meeting_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_schedules');
    }
};
