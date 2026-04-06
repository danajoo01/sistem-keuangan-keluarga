<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @method MorphMany notifications()
 * @method MorphMany readNotifications()
 * @method MorphMany unreadNotifications()
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function menuAccesses(): BelongsToMany
    {
        return $this->belongsToMany(MenuList::class, 'role_menu_access', 'role', 'menu_list_id', 'role', 'id');
    }

    public function hasAccess(string $key): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return $this->menuAccesses()->where('key', $key)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
