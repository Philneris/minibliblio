<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $loanDate = fake()->dateTimeBetween('-30 days', 'now');
        $expectedReturnDate = (clone $loanDate)->modify('+14 days');

        return [
            'member_id' => Member::factory(),
            'book_id' => Book::factory(),
            'loan_date' => $loanDate->format('Y-m-d'),
            'expected_return_date' => $expectedReturnDate->format('Y-m-d'),
            'actual_return_date' => null,
        ];
    }

    /**
     * Emprunt déjà retourné.
     */
    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'actual_return_date' => now()->toDateString(),
        ]);
    }

    /**
     * Emprunt en retard.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'loan_date' => now()->subDays(20)->toDateString(),
            'expected_return_date' => now()->subDays(6)->toDateString(),
            'actual_return_date' => null,
        ]);
    }
}
