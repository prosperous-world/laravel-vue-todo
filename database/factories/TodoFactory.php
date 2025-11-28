<?php

namespace Database\Factories;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    protected $model = Todo::class;

    public function definition(): array
    {
        $dueDate = fake()->optional(0.7)->dateTimeBetween('now', '+30 days');
        
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'completed' => fake()->boolean(30),
            'due_date' => $dueDate ? $dueDate->format('Y-m-d') : null,
        ];
    }
}

