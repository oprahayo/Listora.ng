<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;

#[Fillable(['name', 'display_name'])]
class Role extends Model
{
    public const NAMES = ['agent', 'landlord', 'tenant', 'admin'];

    public const DISPLAY_NAMES = [
        'agent' => 'Agent',
        'landlord' => 'Landlord',
        'tenant' => 'Tenant',
        'admin' => 'Administrator',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('created_at');
    }

    public static function named(string $name): self
    {
        if (! in_array($name, self::NAMES, true)) {
            throw new InvalidArgumentException("Unsupported Listora role [{$name}].");
        }

        return self::query()->firstOrCreate(
            ['name' => $name],
            ['display_name' => self::DISPLAY_NAMES[$name]],
        );
    }
}
