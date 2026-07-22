<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'role_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('last_active_role')->nullable()->after('role');
            $table->string('account_status')->default('active')->after('last_active_role');
        });

        $now = now();
        $roleIds = [];

        foreach ([
            'agent' => 'Agent',
            'landlord' => 'Landlord',
            'tenant' => 'Tenant',
            'admin' => 'Administrator',
        ] as $name => $displayName) {
            $roleIds[$name] = DB::table('roles')->insertGetId([
                'name' => $name,
                'display_name' => $displayName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('users')
            ->select(['id', 'role'])
            ->orderBy('id')
            ->each(function (object $user) use ($roleIds, $now): void {
                if (isset($roleIds[$user->role])) {
                    DB::table('role_user')->insertOrIgnore([
                        'user_id' => $user->id,
                        'role_id' => $roleIds[$user->role],
                        'created_at' => $now,
                    ]);
                }
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('role', 'primary_role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('primary_role', 'role');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['last_active_role', 'account_status']);
        });

        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
