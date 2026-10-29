<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'book_id',
        'loan_date',
        'expected_return_date',
        'actual_return_date',
    ];

    protected $appends = ['is_overdue'];

    protected $casts = [
        'loan_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    /**
     * Le membre qui a emprunté.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Le livre emprunté.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Vérifie si cet emprunt est en cours (non retourné).
     */
    public function isActive(): bool
    {
        return is_null($this->actual_return_date);
    }

    /**
     * Indique si l'emprunt est en retard.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->isActive() && $this->expected_return_date->lt(now()->startOfDay());
    }

    /**
     * Scope : uniquement les emprunts en cours.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('actual_return_date');
    }

    /**
     * Scope : uniquement les emprunts en retard.
     */
    public function scopeOverdue($query)
    {
        return $query->active()->where('expected_return_date', '<', now()->toDateString());
    }
}
