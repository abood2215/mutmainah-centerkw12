<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('chatwoot_conv_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('agent_name')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_assignments');
    }
};
