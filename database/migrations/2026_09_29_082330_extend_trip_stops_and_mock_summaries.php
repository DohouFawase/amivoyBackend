<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table): void {
            $table->string('cover_color', 16)->nullable();
            $table->string('next_label')->nullable();
            $table->json('member_names')->nullable();
            $table->string('display_dates')->nullable();
            $table->unsignedSmallInteger('duration_days')->nullable();
        });

        Schema::table('user_sessions', function (Blueprint $table): void {
            $table->string('access_token_hash', 64)->nullable()->unique();
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->string('icon', 60)->nullable();
            $table->string('color', 16)->nullable();
        });

        Schema::table('activities', function (Blueprint $table): void {
            $table->string('location_label')->nullable();
            $table->string('time_label', 40)->nullable();
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 60)->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
        });

        Schema::table('trip_journal_entries', function (Blueprint $table): void {
            $table->string('day_label')->nullable();
            $table->string('emoji', 24)->nullable();
        });

        Schema::table('trip_places', function (Blueprint $table): void {
            $table->string('lodging_name')->nullable();
            $table->unsignedBigInteger('lodging_price')->nullable();
            $table->string('lodging_currency', 3)->default('XOF');
        });
    }

    public function down(): void
    {
        Schema::table('user_sessions', function (Blueprint $table): void {
            $table->dropUnique(['access_token_hash']);
            $table->dropColumn('access_token_hash');
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropColumn(['icon', 'color']);
        });

        Schema::table('activities', function (Blueprint $table): void {
            $table->dropColumn(['location_label', 'time_label']);
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn(['category', 'description', 'icon', 'rating']);
        });

        Schema::table('trip_journal_entries', function (Blueprint $table): void {
            $table->dropColumn(['day_label', 'emoji']);
        });

        Schema::table('trip_places', function (Blueprint $table): void {
            $table->dropColumn(['lodging_name', 'lodging_price', 'lodging_currency']);
        });

        Schema::table('trips', function (Blueprint $table): void {
            $table->dropColumn(['cover_color', 'next_label', 'member_names', 'display_dates', 'duration_days']);
        });
    }
};
