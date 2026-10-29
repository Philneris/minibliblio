<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        private LoanService $loanService
    ) {}

    /**
     * Liste des emprunts en cours, avec indicateur "en retard" et filtre par membre.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Loan::query()
            ->with(['member', 'book'])
            ->active()
            ->orderBy('expected_return_date');

        if ($memberId = $request->query('member_id')) {
            $query->where('member_id', $memberId);
        }

        $loans = $query->get();

        $loans->each(function ($loan) {
            $loan->is_overdue = $loan->is_overdue;
        });

        return response()->json($loans);
    }

    /**
     * Créer un emprunt (emprunter un livre).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
        ]);

        $member = Member::findOrFail($validated['member_id']);
        $book = Book::findOrFail($validated['book_id']);

        $loan = $this->loanService->borrowBook($member, $book);
        $loan->load(['member', 'book']);

        return response()->json($loan, 201);
    }

    /**
     * Retourner un livre.
     */
    public function update(Request $request, Loan $loan): JsonResponse
    {
        $loan = $this->loanService->returnBook($loan);
        $loan->load(['member', 'book']);

        return response()->json($loan);
    }
}
