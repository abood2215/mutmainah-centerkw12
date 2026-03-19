<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run all CRM Migrations in one batch to ensure relations are handled.
     */
    public function up(): void
    {
        // 1. CRM Clients
        Schema::create('crm_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('source', ['whatsapp', 'instagram', 'referral'])->default('whatsapp');
            $table->enum('stage', ['new', 'contacted', 'interested', 'booked', 'active', 'followup', 'completed'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->unsignedBigInteger('assigned_to')->nullable(); // FK to employees/users
            $table->text('notes')->nullable();
            $table->string('chatwoot_contact_id')->nullable();
            $table->decimal('deal_value', 12, 2)->default(0); 
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Client Notes
        Schema::create('crm_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('crm_clients')->onDelete('cascade');
            $table->unsignedBigInteger('author_id');
            $table->text('content');
            $table->timestamps();
        });

        // 3. CRM Tasks
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('crm_clients')->onDelete('cascade');
            $table->unsignedBigInteger('assigned_to');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'inprogress', 'done'])->default('pending');
            $table->timestamps();
        });

        // 4. CRM Activity Logs
        Schema::create('crm_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('crm_clients')->onDelete('cascade');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->string('action'); // client_created, stage_changed, etc.
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 5. CRM Conversations
        Schema::create('crm_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('crm_clients')->onDelete('cascade');
            $table->string('chatwoot_id')->nullable();
            $table->enum('channel', ['whatsapp', 'instagram', 'phone', 'direct'])->default('whatsapp');
            $table->enum('status', ['open', 'pending', 'resolved'])->default('open');
            $table->string('subject')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        // 6. CRM Messages
        Schema::create('crm_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('crm_conversations')->onDelete('cascade');
            $table->string('chatwoot_id')->nullable();
            $table->enum('direction', ['in', 'out'])->default('in');
            $table->string('message_type')->default('text');
            $table->text('content');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // 7. CRM Campaigns
        Schema::create('crm_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['promotional', 'reminder', 'followup', 'occasion'])->default('promotional');
            $table->enum('status', ['draft', 'scheduled', 'sent'])->default('draft');
            $table->enum('target_filter', ['all', 'stage', 'source', 'inactive', 'consultant', 'specific'])->default('all');
            $table->string('target_value')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 8. CRM Campaign Recipients
        Schema::create('crm_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('crm_campaigns')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('crm_clients')->onDelete('cascade');
            $table->string('chatwoot_conversation_id')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('replied_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_campaign_recipients');
        Schema::dropIfExists('crm_campaigns');
        Schema::dropIfExists('crm_messages');
        Schema::dropIfExists('crm_conversations');
        Schema::dropIfExists('crm_activity_logs');
        Schema::dropIfExists('crm_tasks');
        Schema::dropIfExists('crm_notes');
        Schema::dropIfExists('crm_clients');
    }
};
