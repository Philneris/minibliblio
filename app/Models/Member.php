<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    // Tous les emprunts de ce membre.

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // Emprunts en cours (non retournés) de ce membre.
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->whereNull('actual_return_date');
    }

    // Vérifie si le membre a au moins un emprunt en retard.
    public function hasOverdueLoans(): bool
    {
        return $this->activeLoans()
            ->where('expected_return_date', '<', now()->toDateString())
            ->exists();
    }
}
