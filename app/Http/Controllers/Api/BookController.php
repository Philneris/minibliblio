<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Liste de tous les livres.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::query()->withCount('activeLoans');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $books = $query->orderBy('title')->get();

        $books->each(function ($book) {
            $book->available_copies = $book->total_copies - $book->active_loans_count;
        });

        return response()->json($books);
    }

    /**
     * Créer un nouveau livre.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'total_copies' => 'required|integer|min:1',
        ]);

        $book = Book::create($validated);

        return response()->json($book, 201);
    }

    /**
     * Afficher un livre.
     */
    public function show(Book $book): JsonResponse
    {
        $book->loadCount('activeLoans');
        $book->available_copies = $book->total_copies - $book->active_loans_count;

        return response()->json($book);
    }

    /**
     * Modifier un livre.
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => 'sometimes|required|string|max:13|unique:books,isbn,' . $book->id,
            'total_copies' => 'sometimes|required|integer|min:1',
        ]);

        $book->update($validated);

        return response()->json($book);
    }

    /**
     * Supprimer un livre.
     * R4 : Un livre avec des emprunts en cours ne peut pas être supprimé.
     */
    public function destroy(Book $book, LoanService $loanService): JsonResponse
    {
        $loanService->ensureBookCanBeDeleted($book);

        $book->delete();

        return response()->json(null, 204);
    }
}
