<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table): void {
            $table->string('visibility')->default('private')->index();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table): void {
            $table->dropIndex(['visibility']);
            $table->dropColumn('visibility');
        });
    }
};
