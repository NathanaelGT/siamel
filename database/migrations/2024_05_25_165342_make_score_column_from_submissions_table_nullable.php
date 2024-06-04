<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->unsignedTinyInteger('score')->after('note')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            //
        });
    }
};
