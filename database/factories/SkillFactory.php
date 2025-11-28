<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'category' => $this->faker->word(),
            'proficiency_level' => $this->faker->randomNumber(),
            'is_active' => $this->faker->boolean(),
            'description' => $this->faker->text(),
            'tags' => $this->faker->words(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
