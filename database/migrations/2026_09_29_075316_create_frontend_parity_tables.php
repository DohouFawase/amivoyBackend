<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('circles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('creator_id');
            $table->foreign('creator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('name');
            $table->json('members')->nullable();
            $table->json('member_user_ids')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('creator_id');
        });

        Schema::create('outings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('creator_id');
            $table->foreign('creator_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('circle_id')->nullable();
            $table->foreign('circle_id')->references('id')->on('circles')->nullOnDelete();
            $table->string('title');
            $table->text('place');
            $table->string('location_type')->default('public');
            $table->string('category');
            $table->string('date_label')->nullable();
            $table->string('time_label')->nullable();
            $table->text('note')->nullable();
            $table->string('activity')->nullable();
            $table->unsignedBigInteger('budget_target')->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->json('contributions')->nullable();
            $table->decimal('latitude', 12, 8)->nullable();
            $table->decimal('longitude', 12, 8)->nullable();
            $table->json('guests')->nullable();
            $table->json('participant_user_ids')->nullable();
            $table->json('attending')->nullable();
            $table->json('checked_in')->nullable();
            $table->boolean('started')->default(false);
            $table->boolean('ended')->default(false);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['creator_id', 'date_label']);
            $table->index('circle_id');
        });

        Schema::create('group_activities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('category');
            $table->string('group_id');
            $table->string('group_name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('actor');
            $table->string('time_label')->nullable();
            $table->string('icon')->nullable();
            $table->string('href')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['category', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_activities');
        Schema::dropIfExists('outings');
        Schema::dropIfExists('circles');
    }
};
