<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MenuList extends Model
{
    use HasFactory;

    protected $table = 'menu_list';

    protected $fillable = [
        'name',
        'key',
        'sort_order',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_menu_access', 'menu_list_id', 'role', 'id', 'role');
    }
}
