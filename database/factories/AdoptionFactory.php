<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adoption>
 */
class AdoptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'contact' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'cpf' => $this->faker->numerify('###########'),
            'observations' => $this->faker->text,
            'status' => 'PENDENTE',
        ];
    }
}
