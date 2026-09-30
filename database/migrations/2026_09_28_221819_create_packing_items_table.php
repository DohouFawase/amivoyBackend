<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id');
            $table->foreign('trip_id')->references('id')->on('trips')->cascadeOnDelete();
            $table->uuid('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();
            $table->string('title');
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->boolean('is_packed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['trip_id', 'is_packed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_items');
    }
};
