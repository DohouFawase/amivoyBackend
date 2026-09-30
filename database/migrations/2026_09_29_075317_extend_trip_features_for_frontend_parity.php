<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table): void {
            $table->uuid('circle_id')->nullable()->after('creator_id');
            $table->foreign('circle_id')->references('id')->on('circles')->nullOnDelete();
        });

        Schema::table('invitations', function (Blueprint $table): void {
            $table->uuid('circle_id')->nullable()->after('trip_id');
            $table->foreign('circle_id')->references('id')->on('circles')->nullOnDelete();
            $table->uuid('outing_id')->nullable()->after('circle_id');
            $table->foreign('outing_id')->references('id')->on('outings')->nullOnDelete();
            $table->uuid('recipient_user_id')->nullable()->after('invited_by');
            $table->foreign('recipient_user_id')->references('id')->on('users')->nullOnDelete();
            $table->dateTime('responded_at')->nullable();
            $table->index(['outing_id', 'status']);
            $table->index(['circle_id', 'status']);
        });

        Schema::table('photos', function (Blueprint $table): void {
            $table->uuid('outing_id')->nullable()->after('trip_id');
            $table->foreign('outing_id')->references('id')->on('outings')->nullOnDelete();
            $table->text('caption')->nullable();
            $table->boolean('shared_to_story')->default(false);
            $table->index(['outing_id', 'shared_to_story']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->uuid('outing_id')->nullable()->after('trip_id');
            $table->foreign('outing_id')->references('id')->on('outings')->nullOnDelete();
            $table->uuid('circle_id')->nullable()->after('outing_id');
            $table->foreign('circle_id')->references('id')->on('circles')->nullOnDelete();
        });

        Schema::table('reminders', function (Blueprint $table): void {
            $table->uuid('outing_id')->nullable()->after('trip_id');
            $table->foreign('outing_id')->references('id')->on('outings')->nullOnDelete();
        });

        Schema::table('trip_places', function (Blueprint $table): void {
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->unsignedSmallInteger('position')->nullable();
            $table->date('stay_start')->nullable();
            $table->date('stay_end')->nullable();
            $table->index(['trip_id', 'position']);
        });

        Schema::table('places', function (Blueprint $table): void {
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('place_type')->nullable();
        });

        Schema::table('activities', function (Blueprint $table): void {
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('country')->nullable();
            $table->json('interests')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['country', 'interests']);
        });

        Schema::table('activities', function (Blueprint $table): void {
            $table->dropColumn(['category', 'icon']);
        });

        Schema::table('places', function (Blueprint $table): void {
            $table->dropColumn(['country', 'region', 'place_type']);
        });

        Schema::table('trip_places', function (Blueprint $table): void {
            $table->dropIndex(['trip_id', 'position']);
            $table->dropColumn(['city', 'country', 'position', 'stay_start', 'stay_end']);
        });

        Schema::table('reminders', function (Blueprint $table): void {
            $table->dropForeign(['outing_id']);
            $table->dropColumn('outing_id');
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropForeign(['circle_id']);
            $table->dropForeign(['outing_id']);
            $table->dropColumn(['circle_id', 'outing_id']);
        });

        Schema::table('photos', function (Blueprint $table): void {
            $table->dropIndex(['outing_id', 'shared_to_story']);
            $table->dropForeign(['outing_id']);
            $table->dropColumn(['outing_id', 'caption', 'shared_to_story']);
        });

        Schema::table('invitations', function (Blueprint $table): void {
            $table->dropIndex(['outing_id', 'status']);
            $table->dropIndex(['circle_id', 'status']);
            $table->dropForeign(['recipient_user_id']);
            $table->dropForeign(['outing_id']);
            $table->dropForeign(['circle_id']);
            $table->dropColumn(['responded_at', 'recipient_user_id', 'outing_id', 'circle_id']);
        });

        Schema::table('trips', function (Blueprint $table): void {
            $table->dropForeign(['circle_id']);
            $table->dropColumn('circle_id');
        });
    }
};
