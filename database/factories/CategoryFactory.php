<?php
// database/factories/CategoryFactory.php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        $name = $this->faker->unique()->realText(50);
        return [
            'name' => $name,
            'slug' => Str::slug($name, '-', null),
            'description' => $this->faker->paragraph,
            'parent_id' => null,
        ];
    }

    public function withParent()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => Category::factory(),
            ];
        });
    }

    public function withChildren($count = 4)
    {
        return $this->afterCreating(function (Category $category) use ($count) {
            Category::factory()->count($count)->create([
                'parent_id' => $category->id,
            ]);
        });
    }
}
