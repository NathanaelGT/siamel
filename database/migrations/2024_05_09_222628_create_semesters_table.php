<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('parity');
            $table->year('year');
            $table->string('academic_year')->storedAs(<<<SQLF
                CONCAT("Semester ", `parity`, " TA. ", IF(
                    `parity` = "Ganjil",
                    CONCAT(`year`, " / ", `year` + 1),
                    CONCAT(`year` - 1, " / ", `year`)
                ))
SQLF
            );

            $table->unique(['parity', 'year']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('semester_id')->after('course_id')->constrained();
            $table->dropColumn('year');

            $table->unique(['course_id', 'semester_id', 'parallel']);
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->year('year');
            $table->dropColumn('semester_id');
        });

        Schema::dropIfExists('semesters');
    }
};
