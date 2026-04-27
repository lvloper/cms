<?php

namespace Database\Factories;

use App\Models\Redirection;
use Illuminate\Database\Eloquent\Factories\Factory;

class RedirectionFactory extends Factory
{
    protected $model = Redirection::class;

    public function definition(): array
    {
        $codes = [301, 302, 303, 307, 308];
        
        return [
            'old_url' => '/vieja-' . $this->faker->slug(2),
            'new_url' => '/nueva-' . $this->faker->slug(2),
            'redirect_code' => $this->faker->randomElement($codes),
            'is_active' => $this->faker->boolean(80), // 80% probability of being active
            'description' => $this->faker->optional(0.7)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function external(): static
    {
        return $this->state(fn (array $attributes) => [
            'new_url' => $this->faker->url(),
        ]);
    }
}