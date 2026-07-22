<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'primary_role', 'last_active_role', 'account_status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withPivot('created_at');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(AccountInvitation::class);
    }

    public function hasRole(string $role): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('name', $role);
        }

        return $this->roles()->where('name', $role)->exists();
    }

    /** @param array<int, string> $roles */
    public function hasAnyRole(array $roles): bool
    {
        $roles = array_values(array_unique(array_intersect($roles, Role::NAMES)));

        if ($roles === []) {
            return false;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn (Role $role): bool => in_array($role->name, $roles, true));
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function assignRole(string $role): void
    {
        $roleModel = Role::named($role);

        $this->roles()->syncWithoutDetaching([
            $roleModel->id => ['created_at' => now()],
        ]);

        $this->unsetRelation('roles');
    }

    public function removeRole(string $role): void
    {
        $roleId = Role::query()->where('name', $role)->value('id');

        if ($roleId) {
            $this->roles()->detach($roleId);
            $this->unsetRelation('roles');
        }

        if ($this->last_active_role === $role) {
            $this->forceFill(['last_active_role' => null])->save();
        }
    }
}
