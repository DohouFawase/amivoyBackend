<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_journal_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id');
            $table->foreign('trip_id')->references('id')->on('trips')->cascadeOnDelete();
            $table->uuid('author_id')->nullable();
            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('place_label')->nullable();
            $table->decimal('lat', 12, 6)->nullable();
            $table->decimal('lng', 12, 6)->nullable();
            $table->dateTime('happened_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['trip_id', 'happened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_journal_entries');
    }
};
