<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('token_hash')->unique();
            $table->string('status')->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['user_id', 'role_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_invitations');
    }
};
