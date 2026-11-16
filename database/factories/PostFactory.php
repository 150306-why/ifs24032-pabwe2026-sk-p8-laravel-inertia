<?php

namespace Database\Factories;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(6), '.');

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'category' => fake()->randomElement(['Teknologi', 'Bisnis', 'Gaya Hidup', 'Pendidikan', 'Kesehatan', 'Travel', 'Opini']),
            'content' => '## '.fake()->sentence(4)."\n\n".fake()->paragraph(6)."\n\n- ".fake()->sentence()."\n- ".fake()->sentence(),
            'cover_image' => null,
            'is_published' => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['is_published' => true, 'published_at' => now()]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['is_published' => false, 'published_at' => null]);
    }
}
