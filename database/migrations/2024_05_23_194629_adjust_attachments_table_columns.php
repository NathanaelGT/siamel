<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign('attachments_submission_id_foreign');
            $table->dropColumn('submission_id');
            $table->string('slug')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignId('submission_id')->constrained();
            $table->dropColumn('slug');
        });
    }
};
