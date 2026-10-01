<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'last_name', 'first_name', 'middle_initial', 'gender', 'suffix', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Builds the display name, e.g. "Maria D. Santos Jr."
    public static function buildName(string $first, ?string $middleInitial, string $last, ?string $suffix): string
    {
        $parts = [
            trim($first),
            filled($middleInitial) ? strtoupper($middleInitial) . '.' : null,
            trim($last),
            filled($suffix) ? $suffix : null,
        ];

        return implode(' ', array_filter($parts));
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}