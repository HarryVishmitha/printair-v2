<?php

namespace Database\Factories;

use App\Models\WorkingGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkingGroup>
 */
class WorkingGroupFactory extends Factory
{
    protected $model = WorkingGroup::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => $name,
            'description' => fake()->optional()->sentence(),
            'is_shareable' => true,
            'is_restricted' => false,
            'is_staff_group' => false,
        ];
    }
}

