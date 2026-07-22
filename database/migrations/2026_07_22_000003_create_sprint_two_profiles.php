<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('account_status', 'status');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('primary_role')->nullable()->change();
            $table->timestamp('last_login_at')->nullable()->after('last_active_role');
            $table->timestamp('onboarding_completed_at')->nullable()->after('last_login_at');
        });

        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('individual');
            $table->string('cac_registration_type')->nullable();
            $table->string('cac_registration_number')->nullable();
            $table->string('verification_status')->default('unverified');
            $table->string('primary_email')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('organization_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('member_role')->default('staff');
            $table->string('status')->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'user_id']);
        });

        Schema::rename('agents', 'agent_profiles');

        Schema::table('agent_profiles', function (Blueprint $table): void {
            $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('account_type')->default('individual')->after('organization_id');
            $table->string('operation_type')->nullable()->after('account_type');
            $table->string('operating_state')->nullable()->after('primary_location');
            $table->string('operating_city')->nullable()->after('operating_state');
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->string('verification_status')->default('unverified')->change();
        });

        DB::table('agent_profiles')->update([
            'operating_city' => DB::raw('primary_location'),
            'operation_type' => 'individual_agent',
        ]);
        DB::table('agent_profiles')->where('verification_status', 'verified')->update(['verified_at' => now()]);

        Schema::table('agent_profiles', function (Blueprint $table): void {
            $table->dropColumn('primary_location');
        });

        Schema::create('landlord_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('preferred_name')->nullable();
            $table->string('preferred_contact_method')->default('app');
            $table->string('verification_status')->default('basic');
            $table->timestamps();
        });

        Schema::create('tenant_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('preferred_name')->nullable();
            $table->string('preferred_contact_method')->default('app');
            $table->string('verification_status')->default('basic');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_profiles');
        Schema::dropIfExists('landlord_profiles');

        Schema::table('agent_profiles', function (Blueprint $table): void {
            $table->string('primary_location')->nullable();
        });
        DB::table('agent_profiles')->update(['primary_location' => DB::raw('operating_city')]);

        Schema::table('agent_profiles', function (Blueprint $table): void {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['organization_id', 'account_type', 'operation_type', 'operating_state', 'operating_city', 'verified_at']);
        });
        Schema::rename('agent_profiles', 'agents');

        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organizations');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['last_login_at', 'onboarding_completed_at']);
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('status', 'account_status');
        });
    }
};
