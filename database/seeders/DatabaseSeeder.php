<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Member;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer des livres réalistes
        $books = [
            ['title' => 'Les Misérables', 'author' => 'Victor Hugo', 'isbn' => '9782070409228', 'total_copies' => 3],
            ['title' => 'Le Petit Prince', 'author' => 'Antoine de Saint-Exupéry', 'isbn' => '9782070612758', 'total_copies' => 5],
            ['title' => 'L\'Étranger', 'author' => 'Albert Camus', 'isbn' => '9782070360024', 'total_copies' => 2],
            ['title' => 'Germinal', 'author' => 'Émile Zola', 'isbn' => '9782070400270', 'total_copies' => 2],
            ['title' => 'Madame Bovary', 'author' => 'Gustave Flaubert', 'isbn' => '9782070413119', 'total_copies' => 1],
            ['title' => 'Le Rouge et le Noir', 'author' => 'Stendhal', 'isbn' => '9782070411634', 'total_copies' => 3],
            ['title' => 'Les Fleurs du Mal', 'author' => 'Charles Baudelaire', 'isbn' => '9782070408504', 'total_copies' => 2],
            ['title' => 'Candide', 'author' => 'Voltaire', 'isbn' => '9782070414055', 'total_copies' => 4],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }

        // Créer des membres
        $members = [
            ['name' => 'Alice Dupont', 'email' => 'alice.dupont@email.com'],
            ['name' => 'Bob Martin', 'email' => 'bob.martin@email.com'],
            ['name' => 'Claire Moreau', 'email' => 'claire.moreau@email.com'],
            ['name' => 'David Bernard', 'email' => 'david.bernard@email.com'],
            ['name' => 'Emma Petit', 'email' => 'emma.petit@email.com'],
        ];

        foreach ($members as $member) {
            Member::create($member);
        }
    }
}
