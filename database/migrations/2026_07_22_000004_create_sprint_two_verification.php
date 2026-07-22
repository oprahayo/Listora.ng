<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('verification_type');
            $table->string('status')->default('draft');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->json('draft_data')->nullable();
            $table->text('identity_data')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reviewer_note')->nullable();
            $table->timestamps();
            $table->index(['verification_type', 'status', 'submitted_at']);
        });

        Schema::create('verification_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('verification_request_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum', 64);
            $table->string('status')->default('uploaded');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('otp_challenges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('identifier');
            $table->string('purpose');
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('consumed_at')->nullable();
            $table->string('requested_ip', 45)->nullable();
            $table->timestamps();
            $table->index(['identifier', 'purpose', 'created_at']);
        });

        Schema::create('invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('intended_role');
            $table->nullableMorphs('related');
            $table->string('token_hash', 64)->unique();
            $table->string('status')->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['invited_by', 'status', 'expires_at']);
        });

        if (Schema::hasTable('account_invitations')) {
            $legacyInvitations = DB::table('account_invitations')
                ->join('roles', 'roles.id', '=', 'account_invitations.role_id')
                ->select(['account_invitations.*', 'roles.name as role_name'])
                ->get();

            foreach ($legacyInvitations as $invitation) {
                DB::table('invitations')->insert([
                    'invited_by' => $invitation->invited_by,
                    'email' => $invitation->email,
                    'phone' => $invitation->phone,
                    'intended_role' => $invitation->role_name,
                    'token_hash' => $invitation->token_hash,
                    'status' => $invitation->status,
                    'expires_at' => $invitation->expires_at,
                    'accepted_at' => $invitation->status === 'accepted' ? $invitation->updated_at : null,
                    'accepted_by' => $invitation->status === 'accepted' ? $invitation->user_id : null,
                    'created_at' => $invitation->created_at,
                    'updated_at' => $invitation->updated_at,
                ]);
            }

            Schema::drop('account_invitations');
        }

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->nullableMorphs('auditable');
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['event', 'created_at']);
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('otp_challenges');
        Schema::dropIfExists('verification_documents');
        Schema::dropIfExists('verification_requests');
    }
};
