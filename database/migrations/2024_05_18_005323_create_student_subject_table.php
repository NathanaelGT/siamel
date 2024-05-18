<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_subject', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->timestamp('registered_at');

            $table->unique(['student_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject');
    }
};
