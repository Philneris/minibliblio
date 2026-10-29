<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Liste de tous les membres.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Member::query()->withCount('activeLoans');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->orderBy('name')->get();

        return response()->json($members);
    }

    /**
     * Créer un nouveau membre.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:members,email',
        ]);

        $member = Member::create($validated);

        return response()->json($member, 201);
    }

    /**
     * Afficher un membre.
     */
    public function show(Member $member): JsonResponse
    {
        $member->loadCount('activeLoans');

        return response()->json($member);
    }

    /**
     * Modifier un membre.
     */
    public function update(Request $request, Member $member): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:members,email,' . $member->id,
        ]);

        $member->update($validated);

        return response()->json($member);
    }

    /**
     * Supprimer un membre.
     */
    public function destroy(Member $member): JsonResponse
    {
        if ($member->activeLoans()->count() > 0) {
            return response()->json([
                'message' => 'Ce membre a des emprunts en cours et ne peut pas être supprimé.',
                'errors' => ['member' => ['Ce membre a des emprunts en cours et ne peut pas être supprimé.']],
            ], 409);
        }

        $member->delete();

        return response()->json(null, 204);
    }
}
