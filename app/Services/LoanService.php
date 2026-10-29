<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Validation\ValidationException;

class LoanService
{
    /**
     * Créer un nouvel emprunt.
     *
     * Règles métier appliquées :
     * - R1 : Max 3 emprunts en cours par membre
     * - R2 : Le livre doit avoir des exemplaires disponibles
     * - R3 : Le membre ne doit pas avoir d'emprunts en retard
     *
     * @throws ValidationException
     */
    public function borrowBook(Member $member, Book $book): Loan
    {
        // R3 : Vérifier les emprunts en retard
        if ($member->hasOverdueLoans()) {
            throw ValidationException::withMessages([
                'member_id' => 'Ce membre a des emprunts en retard et ne peut pas emprunter.',
            ]);
        }

        // R1 : Vérifier le maximum de 3 emprunts en cours
        $activeLoansCount = $member->activeLoans()->count();
        if ($activeLoansCount >= 3) {
            throw ValidationException::withMessages([
                'member_id' => 'Ce membre a déjà 3 emprunts en cours (maximum autorisé).',
            ]);
        }

        // R2 : Vérifier la disponibilité des exemplaires
        if ($book->available_copies <= 0) {
            throw ValidationException::withMessages([
                'book_id' => 'Aucun exemplaire disponible pour ce livre.',
            ]);
        }

        $loanDate = now()->toDateString();
        $expectedReturnDate = now()->addDays(14)->toDateString();

        return Loan::create([
            'member_id' => $member->id,
            'book_id' => $book->id,
            'loan_date' => $loanDate,
            'expected_return_date' => $expectedReturnDate,
        ]);
    }

    /**
     * Retourner un livre (enregistrer la date de retour effective).
     *
     * @throws ValidationException
     */
    public function returnBook(Loan $loan): Loan
    {
        if (!$loan->isActive()) {
            throw ValidationException::withMessages([
                'loan' => 'Cet emprunt a déjà été retourné.',
            ]);
        }

        $loan->update([
            'actual_return_date' => now()->toDateString(),
        ]);

        return $loan->fresh();
    }

    /**
     * Vérifier qu'un livre peut être supprimé.
     *
     * R4 : Un livre avec des emprunts en cours ne peut pas être supprimé.
     *
     * @throws ValidationException
     */
    public function ensureBookCanBeDeleted(Book $book): void
    {
        if ($book->activeLoans()->count() > 0) {
            throw ValidationException::withMessages([
                'book' => 'Ce livre a des emprunts en cours et ne peut pas être supprimé.',
            ]);
        }
    }
}
