<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('refresh_token_hash')->nullable();
            $table->string('device_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trips', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('creator_id')->nullable();
            $table->foreign('creator_id')->references('id')->on('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('destination_label')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('currency')->nullable();
            $table->bigInteger('planned_budget')->nullable();
            $table->integer('estimated_members')->nullable();
            $table->string('status')->nullable();
            $table->json('governance_rules')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trip_members', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('role')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('joined_at')->nullable();
            $table->dateTime('left_at')->nullable();
            $table->unique(['trip_id', 'user_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('invited_by')->nullable();
            $table->foreign('invited_by')->references('id')->on('users')->nullOnDelete();
            $table->string('token_hash')->nullable()->unique();
            $table->string('channel')->nullable();
            $table->string('target')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('use_count')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('polls', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->boolean('multiple_choice')->nullable();
            $table->boolean('anonymous')->nullable();
            $table->integer('quorum_percent')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('closes_at')->nullable();
            $table->uuid('winning_option_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('poll_options', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('poll_id')->nullable();
            $table->foreign('poll_id')->references('id')->on('polls')->nullOnDelete();
            $table->string('label')->nullable();
            $table->json('payload')->nullable();
            $table->integer('position')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('poll_answers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('option_id')->nullable();
            $table->foreign('option_id')->references('id')->on('poll_options')->nullOnDelete();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->dateTime('voted_at')->nullable();
            $table->dateTime('changed_at')->nullable();
            $table->unique(['option_id', 'member_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('destination_proposals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('proposed_by')->nullable();
            $table->foreign('proposed_by')->references('id')->on('trip_members')->nullOnDelete();
            $table->string('name')->nullable();
            $table->decimal('lat', 12, 6)->nullable();
            $table->decimal('lng', 12, 6)->nullable();
            $table->text('pitch')->nullable();
            $table->integer('estimated_cost')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exclusion_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('target_member_id')->nullable();
            $table->foreign('target_member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->uuid('requested_by')->nullable();
            $table->foreign('requested_by')->references('id')->on('trip_members')->nullOnDelete();
            $table->uuid('poll_id')->nullable();
            $table->foreign('poll_id')->references('id')->on('polls')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->string('target_type')->nullable();
            $table->uuid('target_id')->nullable();
            $table->string('emoji')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('places', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('provider')->nullable();
            $table->string('provider_place_id')->nullable();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->decimal('lat', 12, 6)->nullable();
            $table->decimal('lng', 12, 6)->nullable();
            $table->string('address')->nullable();
            $table->json('opening_hours')->nullable();
            $table->json('cached_data')->nullable();
            $table->dateTime('cached_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trip_places', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('place_id')->nullable();
            $table->foreign('place_id')->references('id')->on('places')->nullOnDelete();
            $table->uuid('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('trip_members')->nullOnDelete();
            $table->integer('likes_count')->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->unique(['trip_id', 'place_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('trip_place_id')->nullable();
            $table->foreign('trip_place_id')->references('id')->on('trip_places')->nullOnDelete();
            $table->uuid('responsible_id')->nullable();
            $table->foreign('responsible_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->integer('duration_min')->nullable();
            $table->bigInteger('estimated_cost')->nullable();
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activity_attendees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('activity_id')->nullable();
            $table->foreign('activity_id')->references('id')->on('activities')->nullOnDelete();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->string('rsvp')->nullable();
            $table->unique(['activity_id', 'member_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('meeting_points', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->uuid('activity_id')->nullable();
            $table->foreign('activity_id')->references('id')->on('activities')->nullOnDelete();
            $table->string('name')->nullable();
            $table->decimal('lat', 12, 6)->nullable();
            $table->decimal('lng', 12, 6)->nullable();
            $table->dateTime('meet_at')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reminders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->string('type')->nullable();
            $table->string('target_type')->nullable();
            $table->uuid('target_id')->nullable();
            $table->dateTime('fire_at')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('budgets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->bigInteger('total_planned')->nullable();
            $table->string('currency')->nullable();
            $table->integer('version')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('budget_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('budget_id')->nullable();
            $table->foreign('budget_id')->references('id')->on('budgets')->nullOnDelete();
            $table->string('category')->nullable();
            $table->bigInteger('planned_amount')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contributions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->bigInteger('expected_amount')->nullable();
            $table->bigInteger('paid_amount')->nullable();
            $table->string('status')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expenses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('paid_by')->nullable();
            $table->foreign('paid_by')->references('id')->on('trip_members')->nullOnDelete();
            $table->uuid('budget_line_id')->nullable();
            $table->foreign('budget_line_id')->references('id')->on('budget_lines')->nullOnDelete();
            $table->string('title')->nullable();
            $table->bigInteger('amount')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('fx_rate', 12, 6)->nullable();
            $table->string('split_mode')->nullable();
            $table->dateTime('spent_at')->nullable();
            $table->string('place_label')->nullable();
            $table->string('receipt_url')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expense_participants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('expense_id')->nullable();
            $table->foreign('expense_id')->references('id')->on('expenses')->nullOnDelete();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->bigInteger('share_amount')->nullable();
            $table->decimal('share_weight', 12, 6)->nullable();
            $table->unique(['expense_id', 'member_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('settlements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('from_member')->nullable();
            $table->foreign('from_member')->references('id')->on('trip_members')->nullOnDelete();
            $table->uuid('to_member')->nullable();
            $table->foreign('to_member')->references('id')->on('trip_members')->nullOnDelete();
            $table->bigInteger('amount')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('contribution_id')->nullable();
            $table->foreign('contribution_id')->references('id')->on('contributions')->nullOnDelete();
            $table->uuid('settlement_id')->nullable();
            $table->foreign('settlement_id')->references('id')->on('settlements')->nullOnDelete();
            $table->string('provider')->nullable();
            $table->string('provider_ref')->nullable()->unique();
            $table->bigInteger('amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('status')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('payment_id')->nullable();
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->string('event_type')->nullable();
            $table->json('raw_payload')->nullable();
            $table->boolean('signature_valid')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('refunds', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('payment_id')->nullable();
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->bigInteger('amount')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->nullable();
            $table->string('provider_ref')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exchange_rates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('base')->nullable();
            $table->string('quote')->nullable();
            $table->decimal('rate', 12, 6)->nullable();
            $table->string('source')->nullable();
            $table->dateTime('fetched_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->boolean('push_enabled')->nullable();
            $table->boolean('email_enabled')->nullable();
            $table->boolean('sms_enabled')->nullable();
            $table->json('per_type_settings')->nullable();
            $table->time('quiet_from')->nullable();
            $table->time('quiet_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('device_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('platform')->nullable();
            $table->string('fcm_apns_token')->nullable()->unique();
            $table->dateTime('last_seen_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('photos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->string('storage_key')->nullable();
            $table->string('thumbnail_key')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('size_bytes')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('taken_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('photo_comments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('photo_id')->nullable();
            $table->foreign('photo_id')->references('id')->on('photos')->nullOnDelete();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('location_shares', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->string('duration_mode')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('stopped_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('location_points', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('share_id')->nullable();
            $table->foreign('share_id')->references('id')->on('location_shares')->nullOnDelete();
            $table->decimal('lat', 12, 6)->nullable();
            $table->decimal('lng', 12, 6)->nullable();
            $table->float('accuracy_m')->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('emergency_alerts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->decimal('lat', 12, 6)->nullable();
            $table->decimal('lng', 12, 6)->nullable();
            $table->boolean('position_is_last_known')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('triggered_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('emergency_alert_recipients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('alert_id')->nullable();
            $table->foreign('alert_id')->references('id')->on('emergency_alerts')->nullOnDelete();
            $table->uuid('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('trip_members')->nullOnDelete();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('acknowledged_at')->nullable();
            $table->unique(['alert_id', 'member_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('actor_id')->nullable();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->string('action')->nullable();
            $table->string('entity_type')->nullable();
            $table->uuid('entity_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('reporter_id')->nullable();
            $table->foreign('reporter_id')->references('id')->on('users')->nullOnDelete();
            $table->string('target_type')->nullable();
            $table->uuid('target_id')->nullable();
            $table->string('reason')->nullable();
            $table->text('details')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('moderation_actions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('report_id')->nullable();
            $table->foreign('report_id')->references('id')->on('reports')->nullOnDelete();
            $table->uuid('moderator_id')->nullable();
            $table->foreign('moderator_id')->references('id')->on('users')->nullOnDelete();
            $table->string('action')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('offline_sync_queue', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->string('operation')->nullable();
            $table->json('payload')->nullable();
            $table->string('client_op_id')->nullable()->unique();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->nullable();
            $table->integer('max_members')->nullable();
            $table->integer('photo_quota_mb')->nullable();
            $table->json('features')->nullable();
            $table->bigInteger('price')->nullable();
            $table->string('billing_period')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('plan_id')->nullable();
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->string('status')->nullable();
            $table->dateTime('current_period_end')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partners', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('country')->nullable();
            $table->decimal('commission_rate', 12, 6)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('services', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('partner_id')->nullable();
            $table->foreign('partner_id')->references('id')->on('partners')->nullOnDelete();
            $table->uuid('place_id')->nullable();
            $table->foreign('place_id')->references('id')->on('places')->nullOnDelete();
            $table->string('title')->nullable();
            $table->bigInteger('price')->nullable();
            $table->string('currency')->nullable();
            $table->json('availability')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bookings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id')->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete();
            $table->uuid('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $table->uuid('booked_by')->nullable();
            $table->foreign('booked_by')->references('id')->on('trip_members')->nullOnDelete();
            $table->uuid('activity_id')->nullable();
            $table->foreign('activity_id')->references('id')->on('activities')->nullOnDelete();
            $table->integer('quantity')->nullable();
            $table->bigInteger('total_amount')->nullable();
            $table->string('status')->nullable();
            $table->string('partner_ref')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recommendations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $table->decimal('score', 12, 6)->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('services');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('offline_sync_queue');
        Schema::dropIfExists('moderation_actions');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('emergency_alert_recipients');
        Schema::dropIfExists('emergency_alerts');
        Schema::dropIfExists('location_points');
        Schema::dropIfExists('location_shares');
        Schema::dropIfExists('photo_comments');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('settlements');
        Schema::dropIfExists('expense_participants');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('contributions');
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('meeting_points');
        Schema::dropIfExists('activity_attendees');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('trip_places');
        Schema::dropIfExists('places');
        Schema::dropIfExists('reactions');
        Schema::dropIfExists('exclusion_requests');
        Schema::dropIfExists('destination_proposals');
        Schema::dropIfExists('poll_answers');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('trip_members');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('user_sessions');
    }
};
