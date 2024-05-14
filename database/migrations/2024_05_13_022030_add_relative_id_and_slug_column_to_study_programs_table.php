<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            $table->unsignedBigInteger('relative_id')->after('id');
            $table->string('slug')->unique()->after('name');

            $table->unique(['relative_id', 'faculty_id']);
        });
    }

    public function down(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('relative_id');
        });
    }
};
