<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Tambahkan ini

class PantryItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'quantity',
        'unit',
        'user_id', // <-- Tambahkan 'user_id' di sini
    ];

    /**
     * Get the user that owns the pantry item.
     */
    public function user(): BelongsTo // <-- Tambahkan fungsi ini
    {
        return $this->belongsTo(User::class);
    }
}