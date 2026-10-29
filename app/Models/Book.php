<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'total_copies',
    ];

    protected $casts = [
        'total_copies' => 'integer',
    ];

    /**
     * Tous les emprunts de ce livre.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Emprunts en cours (non retournés) de ce livre.
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->whereNull('actual_return_date');
    }

    /**
     * Nombre d'exemplaires disponibles.
     */
    public function getAvailableCopiesAttribute(): int
    {
        return $this->total_copies - $this->activeLoans()->count();
    }
}
