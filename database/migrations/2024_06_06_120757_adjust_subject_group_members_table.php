<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subject_group_members', function (Blueprint $table) {
            $table->id()->first();
            $table->softDeletes();

            // nama index autogenerate kepanjangan, jadi solusinya bikin manual
            $table->unique(
                ['subject_group_id', 'student_id', 'deleted_at'],
                'subject_group_members_group_id_student_id_deleted_at_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('subject_group_members', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('id');
        });
    }
};
