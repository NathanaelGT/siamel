<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('student_can_manage_group')->default(false);
            $table->boolean('student_can_create_group')->default(false);
            $table->unsignedTinyInteger('group_max_members')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('group_max_members');
            $table->dropColumn('student_can_create_group');
            $table->dropColumn('student_can_manage_group');
        });
    }
};
